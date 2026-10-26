<?php

namespace Tests\Feature\PageBuilder;

use App\Models\Element;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ElementControllerTest extends TestCase
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

    public function test_list_elements(): void
    {
        Element::factory()->count(2)->create(['page_id' => $this->page->id]);

        $response = $this->actingAs($this->user)
            ->get(route('page-builder.elements.index', $this->page));

        $response->assertStatus(200);
        $response->assertJsonStructure(['elements']);
    }

    public function test_cannot_list_elements_of_other_users_page(): void
    {
        $otherPage = Page::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->get(route('page-builder.elements.index', $otherPage));

        $response->assertStatus(403);
    }

    public function test_add_element(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('page-builder.elements.store', $this->page), [
                'type' => 'heading',
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['message', 'element']);
        $this->assertDatabaseHas('elements', ['page_id' => $this->page->id, 'type' => 'heading']);
    }

    public function test_cannot_add_element_to_other_users_page(): void
    {
        $otherPage = Page::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)
            ->post(route('page-builder.elements.store', $otherPage), [
                'type' => 'heading',
            ]);

        $response->assertStatus(403);
    }

    public function test_show_element(): void
    {
        $element = Element::factory()->create(['page_id' => $this->page->id]);

        $response = $this->actingAs($this->user)
            ->get(route('page-builder.elements.show', $element));

        $response->assertStatus(200);
        $response->assertJsonStructure(['element']);
    }

    public function test_cannot_show_element_of_other_users_page(): void
    {
        $otherPage = Page::factory()->create(['user_id' => $this->otherUser->id]);
        $element = Element::factory()->create(['page_id' => $otherPage->id]);

        $response = $this->actingAs($this->user)
            ->get(route('page-builder.elements.show', $element));

        $response->assertStatus(403);
    }

    public function test_update_element(): void
    {
        $element = Element::factory()->create(['page_id' => $this->page->id]);

        $response = $this->actingAs($this->user)
            ->put(route('page-builder.elements.update', $element), [
                'name' => 'Updated Element',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('element.name', 'Updated Element');
    }

    public function test_cannot_update_element_of_other_users_page(): void
    {
        $otherPage = Page::factory()->create(['user_id' => $this->otherUser->id]);
        $element = Element::factory()->create(['page_id' => $otherPage->id]);

        $response = $this->actingAs($this->user)
            ->put(route('page-builder.elements.update', $element), ['name' => 'Hacked']);

        $response->assertStatus(403);
    }

    public function test_delete_element(): void
    {
        $element = Element::factory()->create(['page_id' => $this->page->id]);

        $response = $this->actingAs($this->user)
            ->delete(route('page-builder.elements.destroy', $element));

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Elemento excluído');
        $this->assertSoftDeleted($element);
    }

    public function test_duplicate_element(): void
    {
        $element = Element::factory()->create(['page_id' => $this->page->id]);

        $response = $this->actingAs($this->user)
            ->post(route('page-builder.elements.duplicate', $element));

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'element']);
    }

    public function test_reorder_elements(): void
    {
        $el1 = Element::factory()->create(['page_id' => $this->page->id, 'order' => 0]);
        $el2 = Element::factory()->create(['page_id' => $this->page->id, 'order' => 1]);

        $response = $this->actingAs($this->user)
            ->post(route('page-builder.elements.reorder', $this->page), [
                'order' => [
                    ['id' => $el1->id],
                    ['id' => $el2->id],
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Ordem dos elementos atualizada');
    }

    public function test_move_element(): void
    {
        $element = Element::factory()->create(['page_id' => $this->page->id, 'order' => 0]);

        $response = $this->actingAs($this->user)
            ->post(route('page-builder.elements.move', $element), [
                'parent_id' => null,
                'order' => 1,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('element.order', 1);
    }

    public function test_update_settings(): void
    {
        $element = Element::factory()->create(['page_id' => $this->page->id]);

        $response = $this->actingAs($this->user)
            ->put(route('page-builder.elements.settings', $element), [
                'settings' => ['title' => 'New Title'],
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Configurações do elemento atualizadas');
    }

    public function test_update_styles(): void
    {
        $element = Element::factory()->create(['page_id' => $this->page->id]);

        $response = $this->actingAs($this->user)
            ->put(route('page-builder.elements.styles', $element), [
                'styles' => ['color' => 'red'],
            ]);

        $response->assertStatus(200);
    }
}
