<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StarterTemplatesSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_theme_template_create_page_shows_starter_options(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('page-builder.themes.create'));
        $response->assertOk();
        $response->assertSee('Header Básico');
        $response->assertSee('Rodapé Básico');
        $response->assertSee('Página 404');
    }


    public function test_theme_store_applies_starter_template(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('page-builder.themes.store'), [
            'title' => 'Header Teste',
            'type' => 'header',
            'template' => 'header',
        ]);

        $response->assertRedirect();
        $theme = \App\Models\ThemeTemplate::first();
        $this->assertSame('header', $theme->type);
        $this->assertGreaterThan(0, $theme->page->elements()->count());
    }

}