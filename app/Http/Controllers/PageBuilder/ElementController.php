<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Element;
use App\Services\PageBuilder\Core\PageBuilderService;
use App\Services\PageBuilder\Core\ElementManager;
use App\Services\PageBuilder\Core\Renderer;
use App\Services\PageBuilder\Core\WidgetManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ElementController extends Controller
{
    protected PageBuilderService $pageBuilder;
    protected ElementManager $elementManager;
    protected Renderer $renderer;
    protected WidgetManager $widgetManager;

    public function __construct(PageBuilderService $pageBuilder, ElementManager $elementManager, Renderer $renderer, WidgetManager $widgetManager)
    {
        $this->pageBuilder = $pageBuilder;
        $this->elementManager = $elementManager;
        $this->renderer = $renderer;
        $this->widgetManager = $widgetManager;
    }

    public function index(Page $page): JsonResponse
    {
        $this->authorize('view', $page);
        $elements = $page->allElements()->get();

        return response()->json([
            'elements' => $this->elementManager->buildTree($elements),
        ]);
    }

    public function store(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $validated = $request->validate([
            'type' => 'required|string',
            'settings' => 'nullable|array',
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('elements', 'id')->where('page_id', $page->id),
            ],
            'insert_before_id' => [
                'nullable',
                'integer',
                Rule::exists('elements', 'id')->where('page_id', $page->id),
            ],
        ]);

        if (!$this->widgetManager->getWidget($validated['type'])) {
            return response()->json(['error' => 'Tipo de widget não encontrado'], 422);
        }

        $parentId = $validated['parent_id'] ?? null;

        if ($parentId) {
            $parent = Element::where('id', $parentId)->where('page_id', $page->id)->first();
            $parentWidget = $parent ? $this->widgetManager->getWidget($parent->type) : null;
            if (!$parentWidget || !$parentWidget->isContainer()) {
                $parentId = null;
            }
        }

        $settings = $this->pageBuilder->sanitizeSettings($validated['settings'] ?? []);

        $element = $this->elementManager->create(
            $page,
            $validated['type'],
            $settings,
            $parentId,
            $validated['insert_before_id'] ?? null
        );

        return response()->json([
            'message' => 'Elemento adicionado',
            'element' => $element->load('children'),
        ], 201);
    }

    public function show(Element $element): JsonResponse
    {
        $this->authorize('view', $element->page);
        return response()->json([
            'element' => $element->load('children'),
        ]);
    }

    public function update(Request $request, Element $element): JsonResponse
    {
        $this->authorize('update', $element->page);
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'settings' => 'nullable|array',
            'content' => 'nullable|array',
            'styles' => 'nullable|array',
            'responsive_settings' => 'nullable|array',
            'animation' => 'nullable|array',
            'effects' => 'nullable|array',
            'column_size' => 'nullable|string',
            'css_classes' => 'nullable|array',
            'css_id' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
        ]);

        $element = $this->pageBuilder->updateElement($element, $validated);

        return response()->json([
            'message' => 'Elemento atualizado',
            'element' => $element->load('children'),
        ]);
    }

    public function destroy(Element $element): JsonResponse
    {
        $this->authorize('update', $element->page);
        $this->pageBuilder->removeElement($element);

        return response()->json([
            'message' => 'Elemento excluído',
        ]);
    }

    public function duplicate(Element $element): JsonResponse
    {
        $this->authorize('update', $element->page);
        $newElement = $this->pageBuilder->duplicateElement($element);

        return response()->json([
            'message' => 'Elemento duplicado',
            'element' => $newElement->load('children'),
        ]);
    }

    public function reorder(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|integer|exists:elements,id',
            'order.*.children' => 'nullable|array',
        ]);

        $order = $validated['order'];
        $this->applyOrder($order, $page->id, null);

        return response()->json([
            'message' => 'Ordem dos elementos atualizada',
        ]);
    }

    private function applyOrder(array $items, int $pageId, ?int $parentId): void
    {
        foreach ($items as $index => $item) {
            Element::where('id', $item['id'])
                ->where('page_id', $pageId)
                ->update(['order' => $index, 'parent_id' => $parentId]);

            if (!empty($item['children']) && is_array($item['children'])) {
                if (isset($item['children'][0]) && is_int($item['children'][0])) {
                    foreach ($item['children'] as $childIndex => $childId) {
                        Element::where('id', $childId)
                            ->where('page_id', $pageId)
                            ->update(['order' => $childIndex, 'parent_id' => $item['id']]);
                    }
                } else {
                    $this->applyOrder($item['children'], $pageId, $item['id']);
                }
            }
        }
    }

    public function move(Request $request, Element $element): JsonResponse
    {
        $this->authorize('update', $element->page);
        $validated = $request->validate([
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('elements', 'id')->where('page_id', $element->page_id),
            ],
            'order' => 'required|integer|min:0',
        ]);

        $parentId = $validated['parent_id'] ?? null;

        if ($parentId) {
            $parent = Element::find($parentId);
            $parentWidget = $parent ? $this->widgetManager->getWidget($parent->type) : null;
            if (!$parentWidget || !$parentWidget->isContainer()) {
                return response()->json(['error' => 'Só é possível colocar elementos dentro de seções, colunas e outros blocos'], 422);
            }

            $descendantIds = $this->getAllDescendantIds($element->id);
            if (in_array($parentId, $descendantIds)) {
                return response()->json(['error' => 'Não é possível mover um elemento para dentro dele mesmo'], 422);
            }
        }

        $element->parent_id = $parentId;
        $element->order = $validated['order'];
        $element->save();

        return response()->json([
            'message' => 'Elemento movido',
            'element' => $element->fresh()->load('children'),
        ]);
    }

    private function getAllDescendantIds(int $elementId): array
    {
        $ids = [];
        $children = Element::where('parent_id', $elementId)->pluck('id');
        foreach ($children as $childId) {
            $ids[] = $childId;
            $ids = array_merge($ids, $this->getAllDescendantIds($childId));
        }
        return $ids;
    }

    public function updateSettings(Request $request, Element $element): JsonResponse
    {
        $this->authorize('update', $element->page);
        $validated = $request->validate([
            'settings' => 'required|array',
            'content' => 'sometimes|array',
            'styles' => 'sometimes|array',
            'device' => 'nullable|in:desktop,tablet,mobile',
        ]);

        $device = $validated['device'] ?? 'desktop';
        if ($device !== 'desktop') {
            $this->storeDeviceOverrides($element, $device, 'settings', $this->pageBuilder->sanitizeSettings($validated['settings']));

            return response()->json(['message' => 'Configurações do elemento atualizadas', 'element' => $element->fresh()]);
        }

        $element->settings = array_merge(
            $element->settings ?? [],
            $this->pageBuilder->sanitizeSettings($validated['settings'])
        );

        if (isset($validated['content'])) {
            $element->content = $this->pageBuilder->sanitizeContent($validated['content']);
        }

        if (isset($validated['styles'])) {
            $element->styles = $this->pageBuilder->sanitizeStyles($validated['styles']);
        }

        $element->save();
        $element->page->touch();

        return response()->json([
            'message' => 'Configurações do elemento atualizadas',
            'element' => $element->fresh(),
        ]);
    }

    public function updateStyles(Request $request, Element $element): JsonResponse
    {
        $this->authorize('update', $element->page);
        $validated = $request->validate([
            'styles' => 'required|array',
            'device' => 'nullable|in:desktop,tablet,mobile',
        ]);

        $device = $validated['device'] ?? 'desktop';
        if ($device !== 'desktop') {
            $this->storeDeviceOverrides($element, $device, 'styles', $this->pageBuilder->sanitizeStyles($validated['styles']));

            return response()->json(['message' => 'Estilos do elemento atualizados', 'element' => $element->fresh()]);
        }

        $element->styles = array_merge(
            $element->styles ?? [],
            $this->pageBuilder->sanitizeStyles($validated['styles'])
        );
        $element->save();
        $element->page->touch();

        return response()->json([
            'message' => 'Estilos do elemento atualizados',
            'element' => $element->fresh(),
        ]);
    }

    /**
     * Saves tablet/mobile overrides in responsive_settings; a null value removes the override
     * so the device inherits again (mobile -> tablet -> desktop).
     */
    private function storeDeviceOverrides(Element $element, string $device, string $bucket, array $values): void
    {
        $responsive = $element->responsive_settings ?? [];
        $current = $responsive[$device][$bucket] ?? [];

        foreach ($values as $key => $value) {
            if ($value === null) {
                unset($current[$key]);
                // Also drop a value saved by the old "<key>_<device>" scheme, otherwise it would come back
                $legacy = $key . '_' . $device;
                foreach (['settings', 'styles'] as $b) {
                    $data = $element->{$b} ?? [];
                    if (array_key_exists($legacy, $data)) {
                        unset($data[$legacy]);
                        $element->{$b} = $data;
                    }
                }
            } else {
                $current[$key] = $value;
            }
        }

        $responsive[$device][$bucket] = $current;
        $element->responsive_settings = $responsive;
        $element->save();
        $element->page->touch();
    }

    public function renderElement(Request $request, Element $element): JsonResponse
    {
        $this->authorize('view', $element->page);
        $device = in_array($request->query('device'), ['tablet', 'mobile'], true) ? $request->query('device') : 'desktop';
        [$deviceSettings, $deviceStyles] = $element->valuesForDevice($device);
        $widget = $this->widgetManager->getWidget($element->type);

        if (!$widget) {
            return response()->json(['html' => '<!-- Unknown widget -->', 'element_id' => $element->id]);
        }

        try {
            $childrenHtml = '';
            if ($element->children->isNotEmpty()) {
                foreach ($element->children as $child) {
                    $childrenHtml .= $this->renderer->renderSingleElement($child, $device);
                }
            }

            $innerHtml = $widget->renderEditor(
                array_merge($deviceSettings, $deviceStyles),
                array_merge($element->content ?? [], ['children' => $childrenHtml]),
                $deviceStyles
            );
        } catch (\Throwable $e) {
            $innerHtml = '<!-- Render error: ' . htmlspecialchars($e->getMessage()) . ' -->';
        }

        return response()->json([
            'html' => $innerHtml,
            'element_id' => $element->id,
        ]);
    }

    public function controls(Element $element): JsonResponse
    {
        $this->authorize('view', $element->page);
        $widgetControls = $this->renderer->getWidgetControls($element->type);

        if (!$widgetControls) {
            return response()->json(['error' => 'Widget não encontrado'], 404);
        }

        return response()->json([
            'widget' => $widgetControls,
            'element' => $element->load('children'),
        ]);
    }

    public function widgetControls(string $type): JsonResponse
    {
        $widgetControls = $this->renderer->getWidgetControls($type);

        if (!$widgetControls) {
            return response()->json(['error' => 'Widget não encontrado'], 404);
        }

        return response()->json($widgetControls);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page_id' => 'required|integer|exists:pages,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:10240',
        ]);

        $this->authorize('update', Page::findOrFail($validated['page_id']));

        $file = $request->file('image');

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file->getPathname());
        finfo_close($finfo);

        $allowedImageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        if (!in_array($mimeType, $allowedImageMimes)) {
            return response()->json(['error' => 'Tipo de arquivo inválido.'], 422);
        }

        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            default => throw new \RuntimeException('Tipo não suportado'),
        };

        $filename = Str::uuid() . '.' . $extension;
        $path = $file->storeAs('page-builder', $filename, 'public');

        if (!$path) {
            return response()->json(['error' => 'Falha ao fazer upload'], 500);
        }

        $url = parse_url(Storage::disk('public')->url($path), PHP_URL_PATH);

        return response()->json([
            'url' => $url,
            'filename' => $filename,
        ]);
    }

    public function uploadVideo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page_id' => 'required|integer|exists:pages,id',
            'video' => 'required|file|mimes:mp4,webm,ogg|max:51200',
        ]);

        $this->authorize('update', Page::findOrFail($validated['page_id']));

        $file = $request->file('video');

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file->getPathname());
        finfo_close($finfo);

        $allowedVideoMimes = ['video/mp4', 'video/webm', 'video/ogg'];
        if (!in_array($mimeType, $allowedVideoMimes)) {
            return response()->json(['error' => 'Tipo de arquivo de vídeo inválido.'], 422);
        }

        $extension = match ($mimeType) {
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
            'video/ogg' => 'ogg',
            default => throw new \RuntimeException('Tipo não suportado'),
        };

        $filename = Str::uuid() . '.' . $extension;
        $path = $file->storeAs('page-builder/videos', $filename, 'public');

        if (!$path) {
            return response()->json(['error' => 'Falha ao fazer upload do vídeo'], 500);
        }

        $url = parse_url(Storage::disk('public')->url($path), PHP_URL_PATH);

        return response()->json([
            'url' => $url,
            'filename' => $filename,
        ]);
    }

    public function restoreSnapshot(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $validated = $request->validate([
            'elements' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $snapshotIds = $this->collectSnapshotIds($validated['elements']);
            $page->elements()->whereNotIn('id', $snapshotIds)->forceDelete();

            $this->importSnapshotElements($page, $validated['elements'], null);

            $page->touch();

            DB::commit();

            $elements = $page->allElements()->get();

            return response()->json([
                'message' => 'Versão restaurada',
                'elements' => $this->elementManager->buildTree($elements),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Falha ao restaurar a versão: ' . $e->getMessage()], 500);
        }
    }

    protected function collectSnapshotIds(array $elements): array
    {
        $ids = [];
        foreach ($elements as $el) {
            if (isset($el['id'])) $ids[] = $el['id'];
            if (!empty($el['children']) && is_array($el['children'])) {
                $ids = array_merge($ids, $this->collectSnapshotIds($el['children']));
            }
        }
        return $ids;
    }

    protected function importSnapshotElements(Page $page, array $elements, ?int $parentId): void
    {
        foreach ($elements as $index => $snapshot) {
            $existing = null;
            if (isset($snapshot['id'])) {
                $existing = Element::where('id', $snapshot['id'])
                    ->where('page_id', $page->id)
                    ->withTrashed()
                    ->first();
            }

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->update([
                    'parent_id' => $parentId,
                    'type' => $snapshot['type'] ?? $existing->type,
                    'name' => $snapshot['name'] ?? $existing->name,
                    'order' => $index,
                    'settings' => $this->pageBuilder->sanitizeSettings($snapshot['settings'] ?? $existing->settings ?? []),
                    'content' => $this->pageBuilder->sanitizeContent($snapshot['content'] ?? $existing->content ?? []),
                    'styles' => $this->pageBuilder->sanitizeStyles($snapshot['styles'] ?? $existing->styles ?? []),
                    'responsive_settings' => $snapshot['responsive_settings'] ?? $existing->responsive_settings,
                    'animation' => $snapshot['animation'] ?? $existing->animation,
                    'effects' => $snapshot['effects'] ?? $existing->effects,
                    'column_size' => $snapshot['column_size'] ?? $existing->column_size,
                    'css_classes' => $snapshot['css_classes'] ?? $existing->css_classes,
                    'css_id' => $snapshot['css_id'] ?? $existing->css_id,
                ]);
                $element = $existing;
            } else {
                $widget = $this->widgetManager->getWidget($snapshot['type'] ?? '');
                $defaultSettings = $widget ? $widget->getDefaultSettings() : [];

                $element = new Element();
                $element->page_id = $page->id;
                $element->parent_id = $parentId;
                $element->type = $snapshot['type'] ?? 'text';
                $element->name = $snapshot['name'] ?? ($snapshot['type'] ?? 'Element');
                $element->order = $index;
                $element->settings = $this->pageBuilder->sanitizeSettings($snapshot['settings'] ?? $defaultSettings);
                $element->content = $this->pageBuilder->sanitizeContent($snapshot['content'] ?? []);
                $element->styles = $this->pageBuilder->sanitizeStyles($snapshot['styles'] ?? []);
                $element->responsive_settings = $snapshot['responsive_settings'] ?? [];
                $element->animation = $snapshot['animation'] ?? null;
                $element->effects = $snapshot['effects'] ?? null;
                $element->column_size = $snapshot['column_size'] ?? 'col-12';
                $element->css_classes = $snapshot['css_classes'] ?? null;
                $element->css_id = $snapshot['css_id'] ?? null;
                $element->save();
            }

            if (!empty($snapshot['children']) && is_array($snapshot['children'])) {
                $this->importSnapshotElements($page, $snapshot['children'], $element->id);
            }
        }
    }

}
