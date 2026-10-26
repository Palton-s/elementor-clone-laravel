<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ThemeTemplate;
use App\Models\Page;
use App\Services\PageBuilder\Export\SiteExporter;
use App\Services\PageBuilder\Project\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function __construct(protected ProjectService $projects) {}

    public function index(): View
    {
        $user = auth()->user();

        $projects = Project::query()
            ->when(!$user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->withCount(['pages', 'themeTemplates'])
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        $user->defaultProject();

        return view('page-builder.projects.index', compact('projects'));
    }

    public function dashboard(Project $project): View
    {
        $this->authorize('view', $project);

        $project->loadCount(['pages', 'themeTemplates']);

        $pages = $project->pages()->latest()->limit(10)->get();
        $themes = $project->themeTemplates()->latest()->limit(10)->get();

        return view('page-builder.projects.show', compact('project', 'pages', 'themes'));
    }

    public function exportZip(Project $project, SiteExporter $exporter)
    {
        $this->authorize('view', $project);

        try {
            $zipPath = $exporter->exportProject($project);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $filename = (Str::slug($project->name) ?: 'site') . '.zip';

        return response()->download($zipPath, $filename, ['Content-Type' => 'application/zip'])->deleteFileAfterSend(true);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Project::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:1000',
        ]);

        $project = $this->projects->create(auth()->user(), [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if (!empty($validated['location'])) {
            $settings = $project->settings ?? [];
            $settings['location'] = $validated['location'];
            $project->settings = $settings;
            $project->save();
        }

        return response()->json([
            'message' => 'Projeto criado com sucesso',
            'project' => $project,
        ], 201);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $project = $this->projects->update($project, $validated);

        return response()->json([
            'message' => 'Projeto atualizado com sucesso',
            'project' => $project,
        ]);
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);
        abort_if($project->is_default, 422, 'O projeto padrão não pode ser excluído.');

        $this->projects->delete($project);

        return response()->json([
            'message' => 'Projeto removido com sucesso',
        ]);
    }

    public function move(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'type' => ['required', Rule::in(['page', 'theme_template'])],
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $model = match ($validated['type']) {
            'page' => Page::class,
            'theme_template' => ThemeTemplate::class,
        };

        $moved = $model::query()
            ->whereIn('id', $validated['ids'])
            ->where('user_id', $project->user_id)
            ->update(['project_id' => $project->id]);

        return response()->json([
            'message' => "{$moved} item(ns) movido(s)",
            'moved' => $moved,
        ]);
    }
}
