<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\GlobalWidget;
use App\Services\PageBuilder\GlobalWidget\GlobalWidgetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalWidgetController extends Controller
{
    protected GlobalWidgetService $globalWidgetService;

    public function __construct(GlobalWidgetService $globalWidgetService)
    {
        $this->globalWidgetService = $globalWidgetService;
    }

    public function index(): JsonResponse
    {
        $user = auth()->user();
        $widgets = collect($this->globalWidgetService->getAll())
            ->when(!$user->isAdmin(), fn ($items) => $items->where('user_id', $user->id))
            ->values()
            ->all();

        return response()->json([
            'global_widgets' => $widgets,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'settings' => 'nullable|array',
            'content' => 'nullable|array',
            'styles' => 'nullable|array',
            'description' => 'nullable|string|max:500',
        ]);

        $globalWidget = $this->globalWidgetService->create($validated);

        return response()->json([
            'message' => 'Widget global criado',
            'global_widget' => $globalWidget,
        ], 201);
    }

    public function show(GlobalWidget $globalWidget): JsonResponse
    {
        $this->authorizeOwner($globalWidget);
        $globalWidget->load('user');

        return response()->json([
            'global_widget' => $globalWidget,
        ]);
    }

    public function update(Request $request, GlobalWidget $globalWidget): JsonResponse
    {
        $this->authorizeOwner($globalWidget);
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string',
            'settings' => 'nullable|array',
            'content' => 'nullable|array',
            'styles' => 'nullable|array',
            'status' => 'nullable|in:draft,published',
            'description' => 'nullable|string|max:500',
        ]);

        $globalWidget = $this->globalWidgetService->update($globalWidget, $validated);

        return response()->json([
            'message' => 'Widget global atualizado',
            'global_widget' => $globalWidget,
        ]);
    }

    public function destroy(GlobalWidget $globalWidget): JsonResponse
    {
        $this->authorizeOwner($globalWidget);
        $this->globalWidgetService->delete($globalWidget);

        return response()->json([
            'message' => 'Widget global excluído',
        ]);
    }

    public function render(GlobalWidget $globalWidget): \Illuminate\Http\Response
    {
        $this->authorizeOwner($globalWidget);
        $html = $this->globalWidgetService->renderWidget($globalWidget);

        return response($html)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }
}
