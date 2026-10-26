<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Element;
use App\Models\Project;
use App\Services\PageBuilder\Core\PageBuilderService;
use App\Services\PageBuilder\Core\ElementManager;
use App\Services\PageBuilder\Core\Renderer;
use App\Services\PageBuilder\Core\TemplateManager;
use App\Services\PageBuilder\Export\SiteExporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PageController extends Controller
{
    protected PageBuilderService $pageBuilder;
    protected ElementManager $elementManager;
    protected Renderer $renderer;
    protected TemplateManager $templateManager;

    public function __construct(PageBuilderService $pageBuilder, ElementManager $elementManager, Renderer $renderer, TemplateManager $templateManager)
    {
        $this->pageBuilder = $pageBuilder;
        $this->elementManager = $elementManager;
        $this->renderer = $renderer;
        $this->templateManager = $templateManager;
    }

    public function getTemplates(): array
    {
        return $this->templateManager->all();
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        $projectId = $request->integer('project') ?: null;

        $pages = Page::query()
            ->when(!$user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->when($projectId, fn ($query) => $query->where('project_id', $projectId))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $projects = Project::query()
            ->when(!$user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('page-builder.pages.index', compact('pages', 'projects', 'projectId'));
    }

    public function create(): View
    {
        $templates = [];
        foreach ($this->templateManager->all() as $key => $tmpl) {
            if ($key === 'blank') continue;
            $templates[$key] = $tmpl['name'];
        }
        return view('page-builder.pages.create', compact('templates'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('create', Page::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'nullable|in:draft,published',
            'template' => 'nullable|string|max:255',
            'settings' => 'nullable|array',
            'meta_data' => 'nullable|array',
            '_redirect' => 'nullable|in:index,editor',
        ]);

        $page = $this->pageBuilder->createPage($validated);

        $templateKey = $request->input('template');
        if ($templateKey && $this->templateManager->has($templateKey)) {
            $template = $this->templateManager->get($templateKey);
            $page->settings = array_merge(
                $page->settings ?? [],
                $this->pageBuilder->sanitizeSettings($template['settings'])
            );
            $page->save();
            $this->importTemplateElements($page, $template['elements']);
        }

        $redirectTo = $request->input('_redirect', 'index');

        return $redirectTo === 'editor'
            ? redirect()->route('page-builder.editor', $page)
            : redirect()->route('page-builder.pages.index')->with('success', "Página \"{$page->title}\" criada!");
    }

    public function show(Page $page): View
    {
        $this->authorize('view', $page);
        $html = $this->pageBuilder->renderPage($page);
        return view('page-builder.pages.show', compact('page', 'html'));
    }

    public function edit(Page $page): View
    {
        $this->authorize('update', $page);
        return view('page-builder.editor', compact('page'));
    }

    public function listTemplates(): JsonResponse
    {
        return response()->json(['templates' => $this->templateManager->list()]);
    }

    public function applyTemplate(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $templateKey = $request->input('template', 'blank');

        if (!$this->templateManager->has($templateKey)) {
            return response()->json(['error' => 'Modelo não encontrado'], 404);
        }

        $template = $this->templateManager->get($templateKey);

        DB::beginTransaction();
        try {
            $page->allElements()->forceDelete();
            $page->settings = array_merge(
                $page->settings ?? [],
                $this->pageBuilder->sanitizeSettings($template['settings'])
            );
            $page->save();

            $this->importTemplateElements($page, $template['elements']);

            DB::commit();

            return response()->json([
                'message' => "Modelo \"{$template['name']}\" aplicado",
                'page' => $page->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateLayout(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $page->settings = array_merge(
            $page->settings ?? [],
            $this->pageBuilder->sanitizeSettings($validated['settings'])
        );
        $page->save();

        return response()->json([
            'message' => 'Layout atualizado',
            'page' => $page,
        ]);
    }

    public function getGlobalSettings(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $settings = $page->settings ?? [];
        return response()->json([
            'global_colors' => $settings['global_colors'] ?? [],
            'global_fonts' => $settings['global_fonts'] ?? [],
            'system_fonts' => [
                ['name' => 'System Default', 'family' => 'system-ui, -apple-system, sans-serif'],
                ['name' => 'Inter', 'family' => 'Inter, sans-serif'],
                ['name' => 'Roboto', 'family' => 'Roboto, sans-serif'],
                ['name' => 'Open Sans', 'family' => 'Open Sans, sans-serif'],
                ['name' => 'Lato', 'family' => 'Lato, sans-serif'],
                ['name' => 'Montserrat', 'family' => 'Montserrat, sans-serif'],
                ['name' => 'Poppins', 'family' => 'Poppins, sans-serif'],
                ['name' => 'Raleway', 'family' => 'Raleway, sans-serif'],
                ['name' => 'Nunito', 'family' => 'Nunito, sans-serif'],
                ['name' => 'Merriweather', 'family' => 'Merriweather, serif'],
                ['name' => 'Playfair Display', 'family' => 'Playfair Display, serif'],
                ['name' => 'Oswald', 'family' => 'Oswald, sans-serif'],
                ['name' => 'Ubuntu', 'family' => 'Ubuntu, sans-serif'],
                ['name' => 'Crimson Text', 'family' => 'Crimson Text, serif'],
            ],
        ]);
    }

    public function updateGlobalSettings(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $validated = $request->validate([
            'global_colors' => 'nullable|array',
            'global_fonts' => 'nullable|array',
        ]);

        $settings = $page->settings ?? [];
        if (isset($validated['global_colors'])) {
            $settings['global_colors'] = $this->pageBuilder->sanitizeSettings($validated['global_colors']);
        }
        if (isset($validated['global_fonts'])) {
            $settings['global_fonts'] = $this->pageBuilder->sanitizeSettings($validated['global_fonts']);
        }
        $page->settings = $settings;
        $page->save();

        return response()->json([
            'message' => 'Configurações globais atualizadas',
            'global_colors' => $settings['global_colors'] ?? [],
            'global_fonts' => $settings['global_fonts'] ?? [],
        ]);
    }

    protected function importTemplateElements(Page $page, array $elements, ?int $parentId = null, string $widgetType = null): void
    {
        foreach ($elements as $index => $elData) {
            $type = $widgetType ?? $elData['type'];
            $children = $elData['children'] ?? [];

            $element = new Element();
            $element->page_id = $page->id;
            $element->parent_id = $parentId;
            $element->type = $type;
            $element->uuid = (string) \Illuminate\Support\Str::uuid();
            $element->name = $elData['settings']['name'] ?? ucfirst($type);
            $element->settings = $this->pageBuilder->sanitizeSettings($elData['settings'] ?? []);
            $element->content = $this->pageBuilder->sanitizeContent($elData['content'] ?? []);
            $element->styles = $this->pageBuilder->sanitizeStyles($elData['styles'] ?? []);
            $element->order = $index;
            $element->save();

            if ($children) {
                $childType = null;
                if ($type === 'section') $childType = 'column';
                $this->importTemplateElements($page, $children, $element->id, $childType);
            }
        }
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|array',
            'status' => 'nullable|in:draft,published,archived',
            'settings' => 'nullable|array',
            'meta_data' => 'nullable|array',
            'template' => 'nullable|string|max:255',
        ]);

        $page = $this->pageBuilder->updatePage($page, $validated);

        return response()->json([
            'message' => 'Página atualizada',
            'page' => $page,
        ]);
    }

    public function destroy(Page $page): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $page);
        $title = $page->title;
        $page->delete();

        return redirect()->route('page-builder.pages.index')
            ->with('success', "Página \"{$title}\" excluída!");
    }

    public function publish(Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $page->status = 'published';
        $page->save();

        return response()->json([
            'message' => 'Página publicada',
            'page' => $page,
        ]);
    }

    public function unpublish(Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $page->status = 'draft';
        $page->save();

        return response()->json([
            'message' => 'Página despublicada',
            'page' => $page,
        ]);
    }

    public function duplicate(Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $newPage = $page->replicate();
        $newPage->title = $page->title . ' (cópia)';
        $newPage->slug = $page->slug . '-' . uniqid();
        $newPage->status = 'draft';
        $newPage->save();

        $idMap = [];
        foreach ($page->allElements()->get() as $element) {
            $newElement = $element->replicate();
            $newElement->page_id = $newPage->id;
            $newElement->uuid = (string) Str::uuid();
            if ($element->parent_id && isset($idMap[$element->parent_id])) {
                $newElement->parent_id = $idMap[$element->parent_id];
            } else {
                $newElement->parent_id = null;
            }
            $newElement->save();
            $idMap[$element->id] = $newElement->id;
        }

        return response()->json([
            'message' => 'Página duplicada',
            'page' => $newPage->fresh(),
        ]);
    }

    public function export(Page $page)
    {
        $this->authorize('view', $page);
        $data = $this->pageBuilder->exportPage($page);

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = Str::slug($page->title) . '.json';

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportZip(Page $page, SiteExporter $exporter)
    {
        $this->authorize('view', $page);
        $zipPath = $exporter->exportPage($page);
        $filename = (Str::slug($page->title) ?: 'pagina') . '.zip';

        return response()->download($zipPath, $filename, ['Content-Type' => 'application/zip'])->deleteFileAfterSend(true);
    }

    public function import(Request $request): JsonResponse
    {
        $this->authorize('create', Page::class);

        $validated = $request->validate([
            'data' => 'required|array',
            'data.title' => 'required|string|max:255',
            'data.elements' => 'sometimes|array',
        ]);

        $page = $this->pageBuilder->importPage($validated['data']);

        return response()->json([
            'message' => 'Página importada',
            'page' => $page,
        ], 201);
    }

    public function getData(Page $page): JsonResponse
    {
        $this->authorize('view', $page);
        $tree = $this->elementManager->buildTree($page->allElements()->get());

        return response()->json([
            'page' => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'status' => $page->status,
                'settings' => $page->settings,
                'meta_data' => $page->meta_data,
                'template' => $page->template,
                'created_at' => $page->created_at,
                'updated_at' => $page->updated_at,
            ],
            'elements' => $tree,
        ]);
    }

    public function render(Page $page): \Illuminate\Http\Response
    {
        $this->authorize('view', $page);
        $html = $this->pageBuilder->renderPage($page, [
            'with_container' => request('format') !== 'inner',
            'theme' => request('theme', 'default'),
        ]);

        if (request('format') === 'inner') {
            $appUrl = rtrim(config('app.url') ?: url('/'), '/');
            $html = preg_replace_callback(
                '/(src|href)=(["\'])(\/(?:storage|uploads)[^"\']*)\2/i',
                function ($matches) use ($appUrl) {
                    return $matches[1] . '=' . $matches[2] . $appUrl . $matches[3] . $matches[2];
                },
                $html
            );
            $html = preg_replace_callback(
                '/url\((["\']?)(\/(?:storage|uploads)[^)"\']*)\1\)/i',
                function ($matches) use ($appUrl) {
                    return 'url(' . $matches[1] . $appUrl . $matches[2] . $matches[1] . ')';
                },
                $html
            );
        }

        return response($html)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('X-Frame-Options', 'SAMEORIGIN')
            ->header('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->header('X-XSS-Protection', '1; mode=block');
    }
}
