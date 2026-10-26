<?php

namespace App\Services\PageBuilder\Project;

use App\Models\Page;
use App\Models\Project;
use App\Models\ThemeTemplate;
use App\Models\User;
use Illuminate\Support\Str;

class ProjectService
{
    public function create(User $user, array $data): Project
    {
        $name = trim($data['name'] ?? 'Projeto');

        return Project::create([
            'user_id' => $user->id,
            'name' => $name,
            'slug' => $this->generateUniqueSlug($user, $name),
            'description' => $data['description'] ?? null,
            'settings' => $data['settings'] ?? [],
            'is_default' => (bool) ($data['is_default'] ?? false),
        ]);
    }

    public function update(Project $project, array $data): Project
    {
        if (isset($data['name']) && $data['name'] !== $project->name) {
            $project->slug = $this->generateUniqueSlug($project->user, $data['name'], $project->id);
        }

        $project->fill([
            'name' => $data['name'] ?? $project->name,
            'description' => $data['description'] ?? $project->description,
            'settings' => $data['settings'] ?? $project->settings,
        ]);

        $project->save();

        return $project->fresh();
    }

    public function delete(Project $project): void
    {
        $fallback = $project->user->projects()
            ->where('is_default', true)
            ->where('id', '!=', $project->id)
            ->first();

        if (!$fallback) {
            $fallback = $this->create($project->user, [
                'name' => 'Meus Projetos',
                'is_default' => true,
            ]);
        }

        $this->reassign($project, $fallback);
        $project->delete();
    }

    public function reassign(Project $project, ?Project $target): void
    {
        $targetId = $target?->id;

        Page::where('project_id', $project->id)->update(['project_id' => $targetId]);
        ThemeTemplate::where('project_id', $project->id)->update(['project_id' => $targetId]);
    }

    public function ensureDefaultProject(User $user): Project
    {
        return $user->defaultProject();
    }

    protected function generateUniqueSlug(User $user, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'projeto';
        $slug = $base;
        $i = 2;

        while (
            Project::withTrashed()
                ->where('user_id', $user->id)
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
