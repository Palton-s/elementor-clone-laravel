<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageCollection;
use App\Http\Resources\PageResource;
use App\Models\Page;
use App\Services\PageBuilder\Core\PageBuilderService;
use App\Services\PageBuilder\Core\ElementManager;
use App\Services\PageBuilder\Core\TemplateManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageApiController extends Controller
{
    public function __construct(
        protected PageBuilderService $pageBuilder,
        protected ElementManager $elementManager,
        protected TemplateManager $templateManager,
    ) {}

    public function index(Request $request): PageCollection
    {
        $pages = Page::where('user_id', $request->user()->id)
            ->latest()
            ->paginate($request->input('per_page', 20));

        return new PageCollection($pages);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Page::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'nullable|in:draft,published',
            'template' => 'nullable|string|max:255',
            'settings' => 'nullable|array',
            'meta_data' => 'nullable|array',
        ]);

        $page = $this->pageBuilder->createPage($validated);

        $templateKey = $request->input('template');
        if ($templateKey && $this->templateManager->has($templateKey)) {
            $template = $this->templateManager->get($templateKey);
            $page->settings = array_merge($page->settings ?? [], $template['settings']);
            $page->save();
        }

        return response()->json([
            'message' => 'Página criada',
            'page' => new PageResource($page),
        ], 201);
    }

    public function show(Page $page): JsonResponse
    {
        $this->authorize('view', $page);

        $allElements = $page->allElements()->get()->keyBy('id');
        $rootElements = $allElements->whereNull('parent_id')->sortBy('order')->values();
        foreach ($allElements as $el) {
            $children = $allElements->where('parent_id', $el->id)->sortBy('order')->values();
            $el->setRelation('children', $children);
        }
        $page->setRelation('elements', $rootElements);

        return response()->json([
            'page' => new PageResource($page),
        ]);
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|array',
            'status' => 'nullable|in:draft,published',
            'settings' => 'nullable|array',
            'meta_data' => 'nullable|array',
            'template' => 'nullable|string|max:255',
        ]);

        $page = $this->pageBuilder->updatePage($page, $validated);

        return response()->json([
            'message' => 'Página atualizada',
            'page' => new PageResource($page),
        ]);
    }

    public function destroy(Page $page): JsonResponse
    {
        $this->authorize('delete', $page);
        $page->delete();

        return response()->json([
            'message' => 'Página excluída',
        ]);
    }
}
