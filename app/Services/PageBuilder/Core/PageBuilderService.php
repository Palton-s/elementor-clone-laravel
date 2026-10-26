<?php

namespace App\Services\PageBuilder\Core;

use App\Models\Page;
use App\Models\Element;
use App\Models\Revision;
use App\Contracts\PageBuilder\WidgetInterface;
use App\Services\PageBuilder\Security\HtmlSanitizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class PageBuilderService
{
    protected WidgetManager $widgetManager;
    protected ElementManager $elementManager;
    protected Renderer $renderer;
    protected array $config;

    public function __construct(
        WidgetManager $widgetManager,
        ElementManager $elementManager,
        Renderer $renderer
    ) {
        $this->widgetManager = $widgetManager;
        $this->elementManager = $elementManager;
        $this->renderer = $renderer;
        $this->config = config('page-builder');
    }

    /**
     * Criar uma nova página
     */
    public function createPage(array $data): Page
    {
        DB::beginTransaction();

        try {
            $page = new Page();
            $page->fill($data);
            $page->user_id = auth()->id();
            $page->project_id = $data['project_id'] ?? auth()->user()?->defaultProject()?->id;
            $page->status = $data['status'] ?? 'draft';
            $page->slug = $this->generateUniqueSlug($data['title']);
            $page->settings = $this->sanitizeSettings($data['settings'] ?? []);
            $page->meta_data = $this->sanitizeSettings($data['meta_data'] ?? []);
            $page->save();

            // Criar revisão inicial
            $this->createRevision($page, 'Initial creation');

            DB::commit();

            Event::dispatch('page.created', $page);

            return $page;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $base = $slug;
        $counter = 1;

        while (Page::withTrashed()->where('slug', $slug)->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Atualizar página
     */
    public function updatePage(Page $page, array $data): Page
    {
        DB::beginTransaction();

        try {
            // Salvar conteúdo atual antes de modificar
            $oldContent = $page->content;

            $page->fill($data);

            if (isset($data['content'])) {
                $page->content = $this->sanitizeContent($data['content']);
            }

            if (isset($data['settings'])) {
                $page->settings = $this->sanitizeSettings($data['settings']);
            }

            if (isset($data['meta_data'])) {
                $page->meta_data = $this->sanitizeSettings($data['meta_data']);
            }

            $page->save();

            // Criar revisão se conteúdo mudou
            if ($oldContent != $page->content) {
                $this->createRevision($page, 'Content updated');
            }

            DB::commit();

            // Limpar cache
            $this->clearPageCache($page);

            Event::dispatch('page.updated', $page);

            return $page;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function cachePage(string $key, int $ttl, \Closure $callback, Page $page): mixed
    {
        try {
            return Cache::tags("page:{$page->id}")->remember($key, $ttl, $callback);
        } catch (\Throwable) {
            return Cache::remember($key, $ttl, $callback);
        }
    }

    public function renderPage(Page $page, array $options = []): string
    {
        $cacheEnabled = $this->config['template_cache']['enabled'] ?? false;

        if (!$cacheEnabled) {
            return $this->renderer->render($page, $options);
        }

        $timestamp = $page->updated_at?->timestamp ?? $page->created_at?->timestamp ?? time();
        $cacheKey = "page.{$page->id}.v{$timestamp}." . md5(serialize($options)) . '.render';
        $ttl = $this->config['template_cache']['ttl'] ?? 3600;

        return $this->cachePage($cacheKey, $ttl, fn() => $this->renderer->render($page, $options), $page);
    }

    public function renderEditor(Page $page): string
    {
        $cacheEnabled = $this->config['template_cache']['enabled'] ?? false;

        if (!$cacheEnabled) {
            return $this->renderer->renderEditor($page);
        }

        $timestamp = $page->updated_at?->timestamp ?? $page->created_at?->timestamp ?? time();
        $cacheKey = "page.{$page->id}.v{$timestamp}.editor";
        $ttl = $this->config['template_cache']['ttl'] ?? 3600;

        return $this->cachePage($cacheKey, $ttl, fn() => $this->renderer->renderEditor($page), $page);
    }

    public function renderJson(Page $page): array
    {
        $cacheEnabled = $this->config['template_cache']['enabled'] ?? false;

        if (!$cacheEnabled) {
            return $this->renderer->renderJson($page);
        }

        $timestamp = $page->updated_at?->timestamp ?? $page->created_at?->timestamp ?? time();
        $cacheKey = "page.{$page->id}.v{$timestamp}.json";
        $ttl = $this->config['template_cache']['ttl'] ?? 3600;

        return $this->cachePage($cacheKey, $ttl, fn() => $this->renderer->renderJson($page), $page);
    }

    /**
     * Adicionar elemento à página
     */
    public function addElement(Page $page, string $widgetType, array $settings = []): Element
    {
        $widget = $this->widgetManager->getWidget($widgetType);

        if (!$widget) {
            throw new \InvalidArgumentException("Widget {$widgetType} not found");
        }

        DB::beginTransaction();

        try {
            $element = new Element();
            $element->page_id = $page->id;
            $element->type = $widgetType;
            $element->name = $widget->getLabel();
            $element->settings = array_merge($widget->getDefaultSettings(), $settings);
            $element->content = [];
            $element->styles = [];
            $element->order = ((int) ($page->elements()->max('order') ?? -1)) + 1;
            $element->save();

            DB::commit();

            // Limpar cache
            $this->clearPageCache($page);

            Event::dispatch('element.added', $element);

            return $element;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Atualizar elemento
     */
    public function updateElement(Element $element, array $data): Element
    {
        DB::beginTransaction();

        try {
            $element->fill($data);

            // Sanitizar settings
            if (isset($data['settings'])) {
                $element->settings = $this->sanitizeSettings($data['settings']);
            }

            if (isset($data['content'])) {
                $element->content = $this->sanitizeContent($data['content']);
            }

            if (isset($data['styles'])) {
                $element->styles = $this->sanitizeStyles($data['styles']);
            }

            $element->save();

            DB::commit();

            // Limpar cache da página
            $this->clearPageCache($element->page);

            Event::dispatch('element.updated', $element);

            return $element;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remover elemento
     */
    public function removeElement(Element $element): bool
    {
        DB::beginTransaction();

        try {
            $pageId = $element->page_id;

            $descendantIds = $this->getDescendantIds($element->id);

            if (!empty($descendantIds)) {
                Element::whereIn('id', $descendantIds)->delete();
            }

            $element->delete();

            DB::commit();

            // Limpar cache
            $this->clearPageCache(Page::find($pageId));

            Event::dispatch('element.removed', $element);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Duplicar elemento
     */
    public function duplicateElement(Element $element): Element
    {
        DB::beginTransaction();

        try {
            $order = $element->parent_id
                ? Element::where('parent_id', $element->parent_id)->max('order') + 1
                : $element->page->elements()->max('order') + 1;

            $newElement = $this->duplicateElementTree($element, $element->parent_id, $order);

            DB::commit();

            // Limpar cache
            $this->clearPageCache($element->page);

            Event::dispatch('element.duplicated', $newElement);

            return $newElement;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function getDescendantIds(int $elementId): array
    {
        $ids = [];

        foreach (Element::where('parent_id', $elementId)->pluck('id') as $childId) {
            $ids[] = $childId;
            $ids = array_merge($ids, $this->getDescendantIds($childId));
        }

        return $ids;
    }

    protected function duplicateElementTree(Element $element, ?int $parentId, int $order): Element
    {
        $newElement = $element->replicate();
        $newElement->uuid = (string) Str::uuid();
        $newElement->name = $element->name . ' (cópia)';
        $newElement->parent_id = $parentId;
        $newElement->order = $order;
        $newElement->save();

        foreach ($element->children()->orderBy('order')->get() as $child) {
            $this->duplicateElementTree($child, $newElement->id, $child->order);
        }

        return $newElement;
    }

    /**
     * Criar revisão
     */
    protected function createRevision(Page $page, string $label = null): Revision
    {
        $revision = new Revision();
        $revision->page_id = $page->id;
        $revision->user_id = auth()->id();
        $revision->content = $page->content;
        $revision->settings = $page->settings;
        $revision->meta_data = $page->meta_data;
        $revision->elements = $this->snapshotElements($page);
        $revision->version = $this->generateVersionNumber($page);
        $revision->label = $label ?? 'Auto-save';
        $revision->type = 'manual';
        $revision->save();

        // Limitar número de revisões
        $limit = $this->config['revisions']['max_per_page'] ?? 50;
        if ($limit) {
            $page->revisions()
                ->orderBy('created_at', 'desc')
                ->skip($limit)
                ->take(100)
                ->delete();
        }

        return $revision;
    }

    public function snapshotElements(Page $page): array
    {
        return Element::where('page_id', $page->id)
            ->orderBy('order')
            ->get()
            ->map(fn (Element $element) => [
                'id' => $element->id,
                'parent_id' => $element->parent_id,
                'uuid' => $element->uuid,
                'type' => $element->type,
                'name' => $element->name,
                'order' => $element->order,
                'settings' => $element->settings,
                'content' => $element->content,
                'styles' => $element->styles,
                'responsive_settings' => $element->responsive_settings,
                'animation' => $element->animation,
                'effects' => $element->effects,
                'column_size' => $element->column_size,
                'css_classes' => $element->css_classes,
                'css_id' => $element->css_id,
            ])
            ->all();
    }

    public function restoreElementsFromSnapshot(Page $page, array $snapshot): void
    {
        Element::where('page_id', $page->id)->forceDelete();

        $pending = $snapshot;
        $idMap = [];

        while (!empty($pending)) {
            $created = false;

            foreach ($pending as $index => $data) {
                $oldParent = $data['parent_id'] ?? null;

                if ($oldParent !== null && !isset($idMap[$oldParent])) {
                    continue;
                }

                $uuid = $data['uuid'] ?? null;
                if (!$uuid || Element::where('uuid', $uuid)->exists()) {
                    $uuid = (string) Str::uuid();
                }

                $element = new Element();
                $element->page_id = $page->id;
                $element->parent_id = $oldParent !== null ? $idMap[$oldParent] : null;
                $element->uuid = $uuid;
                $element->type = $data['type'];
                $element->name = $data['name'] ?? $data['type'];
                $element->order = $data['order'] ?? 0;
                $element->settings = $this->sanitizeSettings($data['settings'] ?? []);
                $element->content = $this->sanitizeContent($data['content'] ?? []);
                $element->styles = $this->sanitizeStyles($data['styles'] ?? []);
                $element->responsive_settings = $data['responsive_settings'] ?? [];
                $element->animation = $data['animation'] ?? null;
                $element->effects = $data['effects'] ?? null;
                $element->column_size = $data['column_size'] ?? 'col-12';
                $element->css_classes = $data['css_classes'] ?? null;
                $element->css_id = $data['css_id'] ?? null;
                $element->save();

                $idMap[$data['id']] = $element->id;
                unset($pending[$index]);
                $created = true;
            }

            if (!$created) {
                break;
            }
        }
    }

    /**
     * Restaurar revisão
     */
    public function restoreRevision(Page $page, Revision $revision): Page
    {
        abort_unless((int) $revision->page_id === (int) $page->id, 404);

        DB::beginTransaction();

        try {
            $page->content = $revision->content;
            $page->settings = $revision->settings;
            $page->meta_data = $revision->meta_data;
            $page->save();

            if (is_array($revision->elements)) {
                $this->restoreElementsFromSnapshot($page, $revision->elements);
            }

            $this->createRevision($page, 'Restored from revision #' . $revision->id);

            DB::commit();

            // Limpar cache
            $this->clearPageCache($page);

            Event::dispatch('page.restored', ['page' => $page, 'revision' => $revision]);

            return $page;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Gerar número de versão
     */
    protected function generateVersionNumber(Page $page): string
    {
        $lastRevision = $page->revisions()->latest()->first();

        if (!$lastRevision) {
            return '1.0.0';
        }

        $parts = explode('.', $lastRevision->version);
        $parts[2] = ($parts[2] ?? 0) + 1;

        return implode('.', $parts);
    }

    /**
     * Sanitizar conteúdo
     */
    public function sanitizeContent(array $content): array
    {
        return array_map(function ($item) {
            if (is_array($item)) {
                return $this->sanitizeContent($item);
            }

            if (is_string($item) && $this->containsHtml($item)) {
                return HtmlSanitizer::sanitizeRichText($item);
            }

            if (is_string($item)) {
                return htmlspecialchars($item, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
            }

            return $item;
        }, $content);
    }

    /**
     * Sanitizar settings com base no tipo esperado
     */
    public function sanitizeSettings(array $settings): array
    {
        $sanitized = [];

        foreach ($settings as $key => $value) {
            if (is_string($value)) {
                if ($key === 'custom_css') {
                    $sanitized[$key] = HtmlSanitizer::sanitizeCustomCss($value);
                } elseif ($key === 'custom_js') {
                    $sanitized[$key] = HtmlSanitizer::sanitizeCustomJs($value);
                } elseif (str_contains($key, 'color') || str_contains($key, '_color')) {
                    $sanitized[$key] = preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value) ? $value : '#000000';
                } elseif (str_contains($key, 'url') || str_contains($key, 'link') || str_contains($key, 'src')) {
                    $sanitized[$key] = $this->sanitizeUrl($value);
                } elseif (str_contains($key, 'content') || str_contains($key, 'html') || $key === 'content') {
                    $sanitized[$key] = HtmlSanitizer::sanitizeRichText($value);
                } elseif (in_array($key, ['css_classes', 'css_id'], true)) {
                    $sanitized[$key] = HtmlSanitizer::sanitizeIdentifiers($value);
                } elseif (in_array($key, ['name', 'title', 'alt', 'text', 'label'])) {
                    $sanitized[$key] = strip_tags($value);
                } else {
                    $sanitized[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
                }
            } elseif (is_numeric($value)) {
                $sanitized[$key] = is_float($value) ? (float) $value : (int) $value;
            } elseif (is_bool($value)) {
                $sanitized[$key] = $value;
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeSettings($value);
            } elseif (is_null($value)) {
                $sanitized[$key] = null;
            } else {
                $sanitized[$key] = htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
            }
        }

        return $sanitized;
    }

    protected function sanitizeUrl(string $url): string
    {
        // link_target and similar keys also land here; plain words pass through unchanged
        return HtmlSanitizer::sanitizeUrlValue($url);
    }

    public function sanitizeStyles(array $styles): array
    {
        $sanitized = [];

        foreach ($styles as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeStyles($value);
                continue;
            }

            if (!is_string($value)) {
                $sanitized[$key] = $value;
                continue;
            }

            $value = strip_tags($value);
            $value = preg_replace('/expression\s*\(/i', '', $value);
            $value = preg_replace('/(javascript|vbscript)\s*:/i', '', $value);
            $value = preg_replace('/-moz-binding\s*:/i', '', $value);
            $value = preg_replace('/url\s*\(\s*[\'"]?\s*(javascript|vbscript|data:text\/html)[^)]*\)/i', 'none', $value);
            // Style values are written into style="..." attributes by the widgets
            $value = in_array($key, ['css_classes', 'css_id'], true)
                ? HtmlSanitizer::sanitizeIdentifiers($value)
                : HtmlSanitizer::sanitizeCssValue($value);

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    /**
     * Verificar se contém HTML
     */
    protected function containsHtml(string $string): bool
    {
        return $string !== strip_tags($string);
    }

    protected function clearPageCache(Page $page): void
    {
        $page->touch();
        try {
            Cache::tags("page:{$page->id}")->flush();
        } catch (\Throwable) {
        }
    }

    protected function preloadElements(Page $page): void
    {
        $allElements = $page->allElements()->get()->keyBy('id');
        $rootElements = $allElements->whereNull('parent_id')->sortBy('order')->values();

        foreach ($allElements as $element) {
            $children = $allElements->where('parent_id', $element->id)->sortBy('order')->values();
            $element->setRelation('children', $children);
        }

        $page->setRelation('elements', $rootElements);
    }

    public function exportPage(Page $page, array $options = []): array
    {
        $this->preloadElements($page);

        return [
            'version' => '1.0.0',
            'title' => $page->title,
            'slug' => $page->slug,
            'settings' => $page->settings,
            'meta_data' => $page->meta_data,
            'elements' => $this->exportElements($page->elements),
            'exported_at' => now()->toIso8601String(),
            'exported_by' => auth()->user()?->name ?? 'system',
        ];
    }

    /**
     * Exportar elementos recursivamente
     */
    protected function exportElements($elements): array
    {
        $result = [];

        foreach ($elements as $element) {
            $data = [
                'id' => $element->uuid,
                'type' => $element->type,
                'name' => $element->name,
                'settings' => $element->settings,
                'content' => $element->content,
                'styles' => $element->styles,
                'responsive_settings' => $element->responsive_settings,
                'animation' => $element->animation,
                'effects' => $element->effects,
                'column_size' => $element->column_size,
            ];

            if ($element->children->isNotEmpty()) {
                $data['children'] = $this->exportElements($element->children);
            }

            $result[] = $data;
        }

        return $result;
    }

    /**
     * Importar página de JSON
     */
    public function importPage(array $data, ?int $userId = null): Page
    {
        DB::beginTransaction();

        try {
            $page = new Page();
            $page->user_id = $userId ?? auth()->id();
            $page->title = $data['title'] ?? 'Imported Page';
            $page->slug = $this->generateUniqueSlug($page->title);
            $page->settings = $this->sanitizeSettings($data['settings'] ?? []);
            $page->meta_data = $this->sanitizeSettings($data['meta_data'] ?? []);
            $page->status = 'draft';
            $page->save();

            // Importar elementos
            if (isset($data['elements'])) {
                $this->importElements($page, $data['elements']);
            }

            DB::commit();

            return $page;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Importar elementos recursivamente
     */
    protected function importElements(Page $page, array $elements, int $parentId = null): void
    {
        foreach ($elements as $index => $elementData) {
            $widget = $this->widgetManager->getWidget($elementData['type']);

            if (!$widget) {
                continue;
            }

            $uuid = $elementData['id'] ?? null;
            if (!$uuid || Element::where('uuid', $uuid)->exists()) {
                $uuid = (string) Str::uuid();
            }

            $element = new Element();
            $element->page_id = $page->id;
            $element->type = $elementData['type'];
            $element->name = $elementData['name'] ?? $widget->getLabel();
            $element->uuid = $uuid;
            $element->settings = $this->sanitizeSettings($elementData['settings'] ?? $widget->getDefaultSettings());
            $element->content = $this->sanitizeContent($elementData['content'] ?? []);
            $element->styles = $this->sanitizeStyles($elementData['styles'] ?? []);
            $element->responsive_settings = $elementData['responsive_settings'] ?? [];
            $element->animation = $elementData['animation'] ?? null;
            $element->effects = $elementData['effects'] ?? null;
            $element->column_size = $elementData['column_size'] ?? 'col-12';
            $element->order = $index;
            $element->parent_id = $parentId;
            $element->save();

            // Importar filhos recursivamente
            if (isset($elementData['children'])) {
                $this->importElements($page, $elementData['children'], $element->id);
            }
        }
    }
}
