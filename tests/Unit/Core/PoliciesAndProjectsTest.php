<?php

namespace Tests\Unit\Core;

use App\Models\Page;
use App\Models\Project;
use App\Models\ThemeTemplate;
use App\Models\User;
use App\Policies\PagePolicy;
use App\Policies\ProjectPolicy;
use App\Services\PageBuilder\Project\ProjectService;
use App\Services\PageBuilder\Theme\ThemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PoliciesAndProjectsTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'editor'): User
    {
        return User::factory()->create(['role' => $role]);
    }

    // ---------------------------------------------------------------- PagePolicy

    public function test_page_owner_admin_and_strangers(): void
    {
        $policy = new PagePolicy();
        $owner = $this->user();
        $stranger = $this->user();
        $admin = $this->user('admin');
        $page = Page::factory()->create(['user_id' => $owner->id]);

        foreach (['view', 'update', 'delete'] as $ability) {
            $this->assertTrue($policy->{$ability}($owner, $page), "owner can {$ability}");
            $this->assertTrue($policy->{$ability}($admin, $page), "admin can {$ability}");
            $this->assertFalse($policy->{$ability}($stranger, $page), "stranger cannot {$ability}");
        }
        $this->assertFalse($policy->view(null, $page), 'guests cannot view drafts in the builder');
    }

    public function test_viewer_role_cannot_create_or_edit(): void
    {
        $policy = new PagePolicy();
        $viewer = $this->user('viewer');
        $page = Page::factory()->create(['user_id' => $viewer->id]);

        $this->assertFalse($policy->create($viewer));
        $this->assertFalse($policy->update($viewer, $page));
        $this->assertTrue($policy->create($this->user('editor')));
    }

    public function test_ownership_check_survives_string_ids_from_the_database(): void
    {
        // Some MySQL/PDO setups hydrate integer columns as strings
        $owner = $this->user();
        $page = Page::factory()->create(['user_id' => $owner->id]);
        $page->setRawAttributes(array_merge($page->getAttributes(), ['user_id' => (string) $owner->id]));

        $this->assertTrue((new PagePolicy())->update($owner, $page));
        $this->assertTrue((new PagePolicy())->delete($owner, $page));
    }

    public function test_project_policy(): void
    {
        $policy = new ProjectPolicy();
        $owner = $this->user();
        $project = Project::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($policy->view($owner, $project));
        $this->assertTrue($policy->update($owner, $project));
        $this->assertTrue($policy->delete($owner, $project));
        $this->assertFalse($policy->view($this->user(), $project));
        $this->assertFalse($policy->update($this->user(), $project));
        $this->assertTrue($policy->update($this->user('admin'), $project));
        $this->assertFalse($policy->view(null, $project));
    }

    // ------------------------------------------------------------ ProjectService

    public function test_project_slugs_are_unique_per_user(): void
    {
        $service = app(ProjectService::class);
        $user = $this->user();

        $a = $service->create($user, ['name' => 'Meu Site']);
        $b = $service->create($user, ['name' => 'Meu Site']);
        $other = $service->create($this->user(), ['name' => 'Meu Site']);

        $this->assertSame('meu-site', $a->slug);
        $this->assertSame('meu-site-2', $b->slug);
        $this->assertSame('meu-site', $other->slug, 'another user can reuse the slug');
    }

    public function test_renaming_regenerates_the_slug(): void
    {
        $service = app(ProjectService::class);
        $project = $service->create($this->user(), ['name' => 'Antigo']);

        $updated = $service->update($project, ['name' => 'Novo Nome']);

        $this->assertSame('novo-nome', $updated->slug);
    }

    public function test_deleting_a_project_moves_its_content_to_the_default_project(): void
    {
        $service = app(ProjectService::class);
        $user = $this->user();
        $default = $user->defaultProject();
        $project = $service->create($user, ['name' => 'Temporário']);
        $page = Page::factory()->create(['user_id' => $user->id, 'project_id' => $project->id]);

        $service->delete($project);

        $this->assertSoftDeleted($project);
        $this->assertSame($default->id, $page->fresh()->project_id, 'pages are never lost with the project');
    }

    public function test_default_project_is_created_once(): void
    {
        $user = $this->user();

        $first = $user->defaultProject();
        $second = $user->defaultProject();

        $this->assertTrue($first->is($second));
        $this->assertTrue($first->is_default);
    }

    // ----------------------------------------------------------- Theme conditions

    private function template(array $conditions): ThemeTemplate
    {
        return new ThemeTemplate(['conditions' => $conditions, 'type' => 'header']);
    }

    public function test_theme_conditions(): void
    {
        $themes = app(ThemeService::class);

        $this->assertTrue($themes->matchConditions($this->template([]), 'qualquer'), 'no conditions = everywhere');
        $this->assertTrue($themes->matchConditions($this->template([['type' => 'all']]), 'x'));
        $this->assertTrue($themes->matchConditions($this->template([['type' => 'specific', 'value' => 'sobre']]), 'sobre'));
        $this->assertFalse($themes->matchConditions($this->template([['type' => 'specific', 'value' => 'sobre']]), 'contato'));
        $this->assertTrue($themes->matchConditions($this->template([['type' => 'all_pages']]), 'x'));
        $this->assertFalse($themes->matchConditions($this->template([['type' => '404']]), 'x'));
        $this->assertTrue($themes->matchConditions($this->template([['type' => 'specific', 'value' => 'a'], ['type' => 'specific', 'value' => 'b']]), 'b'), 'any condition matches');
    }

    public function test_home_condition_uses_the_configured_home_slug(): void
    {
        config(['page-builder.theme_builder.home_slug' => 'inicio']);
        $themes = app(ThemeService::class);
        $home = $this->template([['type' => 'home']]);

        $this->assertTrue($themes->matchConditions($home, 'inicio'));
        $this->assertFalse($themes->matchConditions($home, 'home'));
    }
}
