<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ElementResource;
use App\Models\Page;
use App\Models\Element;
use App\Services\PageBuilder\Core\PageBuilderService;
use App\Services\PageBuilder\Core\ElementManager;
use App\Services\PageBuilder\Core\Renderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ElementApiController extends Controller
{
    public function __construct(
        protected PageBuilderService $pageBuilder,
        protected ElementManager $elementManager,
        protected Renderer $renderer,
    ) {}

    public function index(Page $page): JsonResponse
    {
        $this->authorize('view', $page);

        $allElements = $page->allElements()->get()->keyBy('id');
        $rootElements = $allElements->whereNull('parent_id')->sortBy('order')->values();
        foreach ($allElements as $el) {
            $children = $allElements->where('parent_id', $el->id)->sortBy('order')->values();
            $el->setRelation('children', $children);
        }

        return response()->json([
            'elements' => ElementResource::collection($rootElements),
        ]);
    }

    public function store(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $validated = $request->validate([
            'type' => 'required|string',
            'settings' => 'nullable|array',
            'parent_id' => 'nullable|integer|exists:elements,id',
        ]);

        $element = $this->elementManager->create(
            $page,
            $validated['type'],
            $validated['settings'] ?? [],
            $validated['parent_id'] ?? null,
        );

        return response()->json([
            'message' => 'Elemento criado',
            'element' => new ElementResource($element->load('children')),
        ], 201);
    }

    public function show(Element $element): JsonResponse
    {
        $this->authorize('view', $element->page);

        return response()->json([
            'element' => new ElementResource($element->load('children')),
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
            'order' => 'nullable|integer|min:0',
        ]);

        $element = $this->pageBuilder->updateElement($element, $validated);

        return response()->json([
            'message' => 'Elemento atualizado',
            'element' => new ElementResource($element->load('children')),
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
}
