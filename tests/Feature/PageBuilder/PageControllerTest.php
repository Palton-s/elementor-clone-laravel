<?php

namespace Tests\Feature\PageBuilder;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    public function test_guest_cannot_access_page_builder(): void
    {
        $this->get(route('page-builder.pages.index'))->assertRedirect(route('login'));
        $this->get(route('page-builder.pages.create'))->assertRedirect(route('login'));
        $this->post(route('page-builder.pages.store'), [])->assertRedirect(route('login'));
    }

    public function test_user_can_list_pages(): void
    {
        Page::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('page-builder.pages.index'));

        $response->assertStatus(200);
        $response->assertViewHas('pages');
    }

    public function test_user_can_view_create_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('page-builder.pages.create'));

        $response->assertStatus(200);
        $response->assertViewHas('templates');
    }

    public function test_user_can_create_page(): void
    {
        $response = $this->actingAs($this->user)->post(route('page-builder.pages.store'), [
            'title' => 'Test Page',
            '_redirect' => 'index',
        ]);

        $response->assertRedirect(route('page-builder.pages.index'));
        $this->assertDatabaseHas('pages', ['title' => 'Test Page', 'user_id' => $this->user->id]);
    }

    public function test_user_can_create_page_with_blank_template(): void
    {
        $response = $this->actingAs($this->user)->post(route('page-builder.pages.store'), [
            'title' => 'Blank Page',
            'template' => 'blank',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pages', ['title' => 'Blank Page']);
    }

    public function test_user_cannot_view_other_users_page(): void
    {
        $page = Page::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->get(route('page-builder.editor', $page));

        $response->assertStatus(403);
    }

    public function test_user_can_view_own_page_editor(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get(route('page-builder.editor', $page));

        $response->assertStatus(200);
    }

    public function test_user_cannot_update_other_users_page(): void
    {
        $page = Page::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->put(route('page-builder.pages.update', $page), ['title' => 'Hacked']);

        $response->assertStatus(403);
    }

    public function test_user_can_update_own_page(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->put(route('page-builder.pages.update', $page), ['title' => 'Updated']);

        $response->assertStatus(200);
        $response->assertJsonPath('page.title', 'Updated');
    }

    public function test_user_cannot_delete_other_users_page(): void
    {
        $page = Page::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->delete(route('page-builder.pages.destroy', $page));

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_page(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->delete(route('page-builder.pages.destroy', $page));

        $response->assertRedirect(route('page-builder.pages.index'));
        $this->assertSoftDeleted($page);
    }

    public function test_user_can_publish_own_page(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id, 'status' => 'draft']);

        $response = $this->actingAs($this->user)
            ->post(route('page-builder.pages.publish', $page));

        $response->assertStatus(200);
        $this->assertEquals('published', $page->fresh()->status);
    }

    public function test_user_can_unpublish_own_page(): void
    {
        $page = Page::factory()->published()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->post(route('page-builder.pages.unpublish', $page));

        $response->assertStatus(200);
        $this->assertEquals('draft', $page->fresh()->status);
    }

    public function test_user_cannot_publish_other_users_page(): void
    {
        $page = Page::factory()->create(['user_id' => $this->otherUser->id, 'status' => 'draft']);

        $response = $this->actingAs($this->user)
            ->post(route('page-builder.pages.publish', $page));

        $response->assertStatus(403);
    }

    public function test_user_can_duplicate_own_page(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->post(route('page-builder.pages.duplicate', $page));

        $response->assertStatus(200);
        $response->assertJsonPath('page.title', $page->title . ' (cópia)');
    }

    public function test_user_can_export_page(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get(route('page-builder.pages.export', $page));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
    }

    public function test_user_can_get_page_data(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get(route('page-builder.pages.data', $page));

        $response->assertStatus(200);
        $response->assertJsonStructure(['page' => ['id', 'title', 'slug', 'status', 'settings']]);
    }

    public function test_user_can_render_page(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get(route('page-builder.render', $page));

        $response->assertStatus(200);
    }

    public function test_user_cannot_export_other_users_page(): void
    {
        $page = Page::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->get(route('page-builder.pages.export', $page));

        $response->assertStatus(403);
    }

    public function test_list_templates(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('page-builder.templates.list'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['templates']);
    }

    public function test_apply_template(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->post(route('page-builder.pages.apply-template', $page), [
                'template' => 'landing',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Modelo "Página de vendas" aplicado');
    }

    public function test_apply_invalid_template_returns_404(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->post(route('page-builder.pages.apply-template', $page), [
                'template' => 'nonexistent',
            ]);

        $response->assertStatus(404);
    }

    public function test_update_layout(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->put(route('page-builder.pages.layout', $page), [
                'settings' => ['container_width' => '1200px'],
            ]);

        $response->assertStatus(200);
    }

    public function test_import_page(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('page-builder.pages.import'), [
                'data' => ['title' => 'Imported'],
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('page.title', 'Imported');
    }
}
