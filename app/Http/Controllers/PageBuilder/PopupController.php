<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Popup;
use App\Services\PageBuilder\Core\PageBuilderService;
use App\Services\PageBuilder\Core\TemplateManager;
use App\Services\PageBuilder\Popup\PopupService;
use App\Services\PageBuilder\Theme\ThemeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PopupController extends Controller
{
    protected PageBuilderService $pageBuilder;
    protected PopupService $popupService;
    protected TemplateManager $templateManager;

    public function __construct(PageBuilderService $pageBuilder, PopupService $popupService, TemplateManager $templateManager)
    {
        $this->pageBuilder = $pageBuilder;
        $this->popupService = $popupService;
        $this->templateManager = $templateManager;
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        $projectId = $request->integer('project') ?: null;

        $popups = Popup::ordered()
            ->with('user', 'page')
            ->when(!$user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->when($projectId, fn ($query) => $query->where('project_id', $projectId))
            ->get();

        $projects = \App\Models\Project::query()
            ->when(!$user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('page-builder.popups.index', compact('popups', 'projects', 'projectId'));
    }

    public function create(): View
    {
        $types = Popup::types();
        $triggerTypes = Popup::triggerTypes();
        $popupTemplates = $this->templateManager->popupTemplateList();
        return view('page-builder.popups.create', compact('types', 'triggerTypes', 'popupTemplates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', Popup::types()),
            'status' => 'nullable|in:draft,published',
            'triggers' => 'nullable|array',
            'settings' => 'nullable|array',
            'template' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        DB::beginTransaction();
        try {
            $pageData = [
                'title' => $validated['title'] . ' (Popup)',
                'status' => 'draft',
            ];
            $page = $this->pageBuilder->createPage($pageData);

            $templateKey = $request->input('template');
            $popupSettings = $validated['settings'] ?? [];
            if ($templateKey && $this->templateManager->hasPopupTemplate($templateKey)) {
                $popupTemplate = $this->templateManager->popupTemplate($templateKey);
                $this->templateManager->applyPopupTemplate($page, $templateKey);
                if (!empty($popupTemplate['popup_settings'])) {
                    $popupSettings = array_merge($popupSettings, $popupTemplate['popup_settings']);
                }
            }

            $slug = Str::slug($validated['title']) . '-' . Str::random(6);

            $popup = Popup::create([
                'user_id' => $user->id,
                'project_id' => $page->project_id,
                'page_id' => $page->id,
                'title' => $validated['title'],
                'slug' => $slug,
                'type' => $validated['type'],
                'status' => $validated['status'] ?? 'draft',
                'triggers' => $validated['triggers'] ?? [],
                'settings' => $popupSettings,
                'conditions' => [],
                'order' => Popup::count(),
            ]);

            DB::commit();

            $this->popupService->clearCache();

            $redirectTo = $request->input('next', 'index');

            if ($redirectTo === 'editor') {
                return redirect()->route('page-builder.popups.editor', $popup)
                    ->with('success', "Popup \"{$popup->title}\" criado!");
            }

            return redirect()->route('page-builder.popups.index')
                ->with('success', "Popup \"{$popup->title}\" criado!");
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function edit(Popup $popup): View
    {
        $this->authorizeOwner($popup);
        $types = Popup::types();
        $triggerTypes = Popup::triggerTypes();
        return view('page-builder.popups.create', compact('popup', 'types', 'triggerTypes'));
    }

    public function update(Request $request, Popup $popup): RedirectResponse
    {
        $this->authorizeOwner($popup);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', Popup::types()),
            'status' => 'nullable|in:draft,published',
        ]);

        $popup->update($validated);

        if ($request->boolean('publish') || ($validated['status'] ?? null) === 'published') {
            $popup->update([
                'status' => 'published',
                'published_at' => now(),
            ]);
        }

        $this->popupService->clearCache();

        return redirect()->route('page-builder.popups.index')
            ->with('success', "Popup \"{$popup->title}\" atualizado!");
    }

    public function destroy(Popup $popup): RedirectResponse
    {
        $this->authorizeOwner($popup);
        $title = $popup->title;
        $popup->delete();

        $this->popupService->clearCache();

        return redirect()->route('page-builder.popups.index')
            ->with('success', "Popup \"{$title}\" excluído!");
    }

    public function editor(Popup $popup): View
    {
        $this->authorizeOwner($popup);
        $page = $popup->page;
        if (!$page) {
            abort(404, 'Página associada não encontrada');
        }

        $popupContext = [
            'popup_id' => $popup->id,
            'popup_title' => $popup->title,
            'popup_type' => $popup->type,
        ];

        return view('page-builder.editor', [
            'page' => $page,
            'popupContext' => $popupContext,
        ]);
    }

    public function getTriggers(Popup $popup): JsonResponse
    {
        $this->authorizeOwner($popup);
        return response()->json([
            'triggers' => $popup->triggers ?? [],
            'triggerTypes' => Popup::triggerTypes(),
        ]);
    }

    public function updateTriggers(Request $request, Popup $popup): JsonResponse
    {
        $this->authorizeOwner($popup);
        $validated = $request->validate([
            'triggers' => 'nullable|array',
            'triggers.*.type' => 'required|string',
            'triggers.*.value' => 'nullable|string',
        ]);

        $popup->update([
            'triggers' => $validated['triggers'] ?? [],
        ]);

        $this->popupService->clearCache();

        return response()->json([
            'message' => 'Regras de exibição atualizadas!',
            'triggers' => $popup->fresh()->triggers,
        ]);
    }

    public function getConditions(Popup $popup): JsonResponse
    {
        $this->authorizeOwner($popup);
        return response()->json([
            'conditions' => $popup->conditions ?? [],
            'options' => ThemeService::conditionOptions(),
            'pages' => Page::select('id', 'title', 'slug')
                ->where('status', 'published')
                ->when(!auth()->user()->isAdmin(), fn ($query) => $query->where('user_id', auth()->id()))
                ->orderBy('title')
                ->get()
                ->map(fn($p) => ['id' => $p->id, 'title' => $p->title, 'slug' => $p->slug]),
        ]);
    }

    public function updateConditions(Request $request, Popup $popup): JsonResponse
    {
        $this->authorizeOwner($popup);
        $validated = $request->validate([
            'conditions' => 'nullable|array',
            'conditions.*.type' => 'required|string',
            'conditions.*.value' => 'nullable|string',
        ]);

        $popup->update([
            'conditions' => $validated['conditions'] ?? [],
        ]);

        $this->popupService->clearCache();

        return response()->json([
            'message' => 'Regras de exibição atualizadas!',
            'conditions' => $popup->fresh()->conditions,
        ]);
    }

    public function publish(Popup $popup): JsonResponse
    {
        $this->authorizeOwner($popup);
        $popup->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->popupService->clearCache();

        return response()->json([
            'message' => 'Popup publicado!',
            'popup' => $popup->fresh(),
        ]);
    }

    public function unpublish(Popup $popup): JsonResponse
    {
        $this->authorizeOwner($popup);
        $popup->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        $this->popupService->clearCache();

        return response()->json([
            'message' => 'Popup despublicado!',
            'popup' => $popup->fresh(),
        ]);
    }

    public function render(Popup $popup): \Illuminate\Http\Response
    {
        $this->authorizeOwner($popup);
        $html = $this->popupService->renderPopup($popup);

        return response($html)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }
}
