<?php

namespace Tests\Feature\PageBuilder;

use App\Models\Page;
use App\Models\Revision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevisionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;
    protected Page $page;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->page = Page::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_list_revisions(): void
    {
        Revision::factory()->count(3)->create(['page_id' => $this->page->id, 'user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get(route('page-builder.revisions.index', $this->page));

        $response->assertStatus(200);
        $response->assertJsonStructure(['revisions']);
    }

    public function test_cannot_list_revisions_of_other_users_page(): void
    {
        $otherPage = Page::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->get(route('page-builder.revisions.index', $otherPage));

        $response->assertStatus(403);
    }

    public function test_show_revision(): void
    {
        $revision = Revision::factory()->create(['page_id' => $this->page->id, 'user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get(route('page-builder.revisions.show', $revision));

        $response->assertStatus(200);
        $response->assertJsonStructure(['revision']);
    }

    public function test_restore_revision(): void
    {
        $revision = Revision::factory()->create([
            'page_id' => $this->page->id,
            'user_id' => $this->user->id,
            'content' => ['key' => 'restored-content'],
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('page-builder.revisions.restore', [$this->page, $revision]));

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Revisão restaurada');
    }

    public function test_cannot_restore_revision_of_other_users_page(): void
    {
        $otherPage = Page::factory()->create(['user_id' => $this->otherUser->id]);
        $revision = Revision::factory()->create(['page_id' => $otherPage->id, 'user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->post(route('page-builder.revisions.restore', [$otherPage, $revision]));

        $response->assertStatus(403);
    }

    public function test_diff_revision(): void
    {
        $revision = Revision::factory()->create(['page_id' => $this->page->id, 'user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get(route('page-builder.revisions.diff', [$this->page, $revision]));

        $response->assertStatus(200);
        $response->assertJsonStructure(['current', 'version']);
    }

    public function test_delete_revision(): void
    {
        $revision = Revision::factory()->create(['page_id' => $this->page->id, 'user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->delete(route('page-builder.revisions.destroy', $revision));

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Revisão excluída');
    }

    public function test_prune_revisions(): void
    {
        Revision::factory()->count(10)->create(['page_id' => $this->page->id, 'user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->post(route('page-builder.revisions.prune', $this->page), [
                'keep' => 5,
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'remaining']);
    }

    public function test_auto_save(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('page-builder.revisions.auto-save', $this->page), [
                'content' => ['key' => 'value'],
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Salvamento automático agendado');
        $this->assertDatabaseHas('revisions', [
            'page_id' => $this->page->id,
            'type' => 'auto_save',
        ]);
    }
}
