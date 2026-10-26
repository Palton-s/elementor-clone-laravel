<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\ThemeTemplate;
use App\Services\PageBuilder\Core\PageBuilderService;
use App\Services\PageBuilder\Core\Renderer;
use App\Services\PageBuilder\Core\TemplateManager;
use App\Services\PageBuilder\Theme\ThemeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ThemeTemplateController extends Controller
{
    protected PageBuilderService $pageBuilder;
    protected Renderer $renderer;
    protected TemplateManager $templateManager;
    protected ThemeService $themeService;

    public function __construct(
        PageBuilderService $pageBuilder,
        Renderer $renderer,
        TemplateManager $templateManager,
        ThemeService $themeService
    ) {
        $this->pageBuilder = $pageBuilder;
        $this->renderer = $renderer;
        $this->templateManager = $templateManager;
        $this->themeService = $themeService;
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        $projectId = $request->integer('project') ?: null;

        $templates = ThemeTemplate::ordered()
            ->with('user', 'page')
            ->when(!$user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->when($projectId, fn ($query) => $query->where('project_id', $projectId))
            ->get();
        $grouped = $templates->groupBy('type');

        $projects = \App\Models\Project::query()
            ->when(!$user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('page-builder.theme-templates.index', compact('templates', 'grouped', 'projects', 'projectId'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $types = ThemeTemplate::typeOptions();
        $pages = Page::select('id', 'title')
            ->when(!$user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('title')
            ->get();
        $themeTemplates = $this->templateManager->themeTemplateList();
        return view('page-builder.theme-templates.create', compact('types', 'pages', 'themeTemplates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', ThemeTemplate::types()),
            'page_id' => 'nullable|exists:pages,id',
            'status' => 'nullable|in:draft,published',
            'template' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        DB::beginTransaction();
        try {
            $page = null;
            if (!empty($validated['page_id'])) {
                $page = Page::findOrFail($validated['page_id']);
                $this->authorizeOwner($page);
            } else {
                $pageData = [
                    'title' => $validated['title'] . ' (' . ($validated['type'] ?? 'template') . ')',
                    'status' => 'draft',
                ];
                $page = $this->pageBuilder->createPage($pageData);
            }

            $templateKey = $request->input('template');
            if ($templateKey && empty($validated['page_id']) && $this->templateManager->hasThemeTemplate($templateKey)) {
                $this->templateManager->applyThemeTemplate($page, $templateKey);
            }

            $slug = Str::slug($validated['title']) . '-' . Str::random(6);

            $template = ThemeTemplate::create([
                'user_id' => $user->id,
                'project_id' => $page->project_id,
                'page_id' => $page->id,
                'title' => $validated['title'],
                'slug' => $slug,
                'type' => $validated['type'],
                'status' => $validated['status'] ?? 'draft',
                'conditions' => [],
                'order' => ThemeTemplate::byType($validated['type'])->count(),
            ]);

            DB::commit();

            $this->themeService->clearCache();

            $redirectTo = $request->input('next', 'index');

            if ($redirectTo === 'editor') {
                return redirect()->route('page-builder.themes.editor', $template)
                    ->with('success', "Modelo do tema \"{$template->title}\" criado!");
            }

            return redirect()->route('page-builder.themes.index')
                ->with('success', "Modelo do tema \"{$template->title}\" criado!");
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function edit(ThemeTemplate $themeTemplate): View
    {
        $this->authorizeOwner($themeTemplate);
        $types = ThemeTemplate::typeOptions();
        $pages = Page::select('id', 'title')->orderBy('title')->get();
        return view('page-builder.theme-templates.create', compact('themeTemplate', 'types', 'pages'));
    }

    public function update(Request $request, ThemeTemplate $themeTemplate): RedirectResponse
    {
        $this->authorizeOwner($themeTemplate);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', ThemeTemplate::types()),
            'status' => 'nullable|in:draft,published',
        ]);

        $themeTemplate->update($validated);

        if ($request->boolean('publish') || ($validated['status'] ?? null) === 'published') {
            $themeTemplate->update([
                'status' => 'published',
                'published_at' => now(),
            ]);
        }

        $this->themeService->clearCache();

        return redirect()->route('page-builder.themes.index')
            ->with('success', "Modelo do tema \"{$themeTemplate->title}\" atualizado!");
    }

    public function destroy(ThemeTemplate $themeTemplate): RedirectResponse
    {
        $this->authorizeOwner($themeTemplate);
        $title = $themeTemplate->title;
        $themeTemplate->delete();

        $this->themeService->clearCache();

        return redirect()->route('page-builder.themes.index')
            ->with('success', "Modelo do tema \"{$title}\" excluído!");
    }

    public function editor(ThemeTemplate $themeTemplate): View
    {
        $this->authorizeOwner($themeTemplate);
        $page = $themeTemplate->page;
        if (!$page) {
            abort(404, 'Página associada não encontrada');
        }

        $themeContext = [
            'template_id' => $themeTemplate->id,
            'template_type' => $themeTemplate->type,
            'template_title' => $themeTemplate->title,
        ];

        return view('page-builder.editor', [
            'page' => $page,
            'themeContext' => $themeContext,
        ]);
    }

    public function editConditions(ThemeTemplate $themeTemplate): View
    {
        $this->authorizeOwner($themeTemplate);
        return view('page-builder.theme-templates.conditions', compact('themeTemplate'));
    }

    public function getConditions(ThemeTemplate $themeTemplate): JsonResponse
    {
        $this->authorizeOwner($themeTemplate);
        return response()->json([
            'conditions' => $themeTemplate->conditions ?? [],
            'options' => ThemeService::conditionOptions(),
            'pages' => Page::select('id', 'title', 'slug')
                ->where('status', 'published')
                ->orderBy('title')
                ->get()
                ->map(fn($p) => ['id' => $p->id, 'title' => $p->title, 'slug' => $p->slug]),
        ]);
    }

    public function updateConditions(Request $request, ThemeTemplate $themeTemplate): JsonResponse
    {
        $this->authorizeOwner($themeTemplate);
        $validated = $request->validate([
            'conditions' => 'nullable|array',
            'conditions.*.type' => 'required|string',
            'conditions.*.value' => 'nullable|string',
        ]);

        $themeTemplate->update([
            'conditions' => $validated['conditions'] ?? [],
        ]);

        $this->themeService->clearCache();

        return response()->json([
            'message' => 'Regras de exibição atualizadas!',
            'conditions' => $themeTemplate->fresh()->conditions,
        ]);
    }

    public function publish(ThemeTemplate $themeTemplate): JsonResponse
    {
        $this->authorizeOwner($themeTemplate);
        $themeTemplate->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->themeService->clearCache();

        return response()->json([
            'message' => 'Modelo do tema publicado!',
            'template' => $themeTemplate->fresh(),
        ]);
    }

    public function unpublish(ThemeTemplate $themeTemplate): JsonResponse
    {
        $this->authorizeOwner($themeTemplate);
        $themeTemplate->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        $this->themeService->clearCache();

        return response()->json([
            'message' => 'Modelo do tema despublicado!',
            'template' => $themeTemplate->fresh(),
        ]);
    }

    public function render(ThemeTemplate $themeTemplate): \Illuminate\Http\Response
    {
        $this->authorizeOwner($themeTemplate);
        $html = $this->renderer->render($themeTemplate->page, [
            'with_container' => request('format') !== 'inner',
            'theme' => 'theme-template-' . $themeTemplate->type,
        ]);

        return response($html)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }
}
