<?php

namespace Tests\Unit\Core;

use App\Jobs\AutoSaveRevisionJob;
use App\Jobs\ClearPageCacheJob;
use App\Jobs\ImportHtmlJob;
use App\Models\Element;
use App\Models\GlobalWidget;
use App\Models\Page;
use App\Models\Revision;
use App\Models\User;
use App\Services\PageBuilder\Core\ElementManager;
use App\Services\PageBuilder\Core\Renderer;
use App\Services\PageBuilder\Core\WidgetManager;
use App\Services\PageBuilder\GlobalWidget\GlobalWidgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RenderingServicesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Page $page;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->page = Page::factory()->create(['user_id' => $this->user->id, 'title' => 'Página X', 'status' => 'published']);
    }

    private function add(string $type, array $settings = [], ?int $parent = null): Element
    {
        return app(ElementManager::class)->create($this->page, $type, $settings, $parent);
    }

    private function render(): string
    {
        return app(Renderer::class)->render($this->page->fresh(), ['with_container' => true]);
    }

    // ------------------------------------------------------------------ Renderer

    public function test_full_document_has_title_styles_and_csp(): void
    {
        $this->add('heading', ['title' => 'Olá']);

        $html = $this->render();

        $this->assertStringStartsWith('<!DOCTYPE html>', ltrim($html));
        $this->assertStringContainsString('<title>Página X</title>', $html);
        $this->assertStringContainsString('Content-Security-Policy', $html);
        $this->assertStringContainsString('Olá', $html);
    }

    public function test_youtube_embed_keeps_the_video_options(): void
    {
        $this->add('video', ['video_type' => 'youtube', 'video_url' => 'https://www.youtube.com/watch?v=aGhRCf0Hk5Y',
            'autoplay' => true, 'mute' => true, 'start_time' => 30, 'controls' => false]);

        preg_match('/<iframe[^>]+src="([^"]+)"/', $this->render(), $m);

        $this->assertNotEmpty($m, 'iframe rendered');
        $src = html_entity_decode($m[1]);
        $this->assertStringStartsWith('https://www.youtube.com/embed/aGhRCf0Hk5Y', $src);
        foreach (['autoplay=1', 'mute=1', 'start=30', 'controls=0'] as $param) {
            $this->assertStringContainsString($param, $src, "{$param} must survive embed processing");
        }
    }

    public function test_math_assets_only_load_when_a_formula_exists(): void
    {
        $this->add('heading');
        $this->assertStringNotContainsString('katex', $this->render());

        $this->add('math', ['formula' => 'x^2']);
        $this->assertStringContainsString('katex', $this->render());
    }

    public function test_containers_render_children_in_order(): void
    {
        $section = $this->add('section');
        $col = $this->add('column', [], $section->id);
        $this->add('heading', ['title' => 'Primeiro'], $col->id);
        $this->add('heading', ['title' => 'Segundo'], $col->id);

        $html = $this->render();

        $this->assertLessThan(strpos($html, 'Segundo'), strpos($html, 'Primeiro'));
        $this->assertStringContainsString('data-element-type="column"', $html);
    }

    public function test_hidden_on_device_emits_visibility_css(): void
    {
        $el = $this->add('heading', ['visibility_mobile' => false]);

        $this->assertStringContainsString('@media(max-width:767px){.pb-element[data-element-id="' . $el->id . '"]{display:none !important;}}', $this->render());
    }

    public function test_dynamic_tags_are_expanded(): void
    {
        $this->add('heading', ['title' => 'Bem-vindo a {{page.title}}']);

        $this->assertStringContainsString('Bem-vindo a Página X', $this->render());
    }

    public function test_unknown_widget_types_do_not_break_the_page(): void
    {
        Element::factory()->create(['page_id' => $this->page->id, 'type' => 'widget_removido', 'order' => 0]);
        $this->add('heading', ['title' => 'Continua']);

        $html = $this->render();

        $this->assertStringContainsString('Unknown widget type: widget_removido', $html);
        $this->assertStringContainsString('Continua', $html);
    }

    public function test_widget_controls_are_exposed_for_the_editor(): void
    {
        $controls = app(Renderer::class)->getWidgetControls('heading');

        $this->assertSame('heading', $controls['type']);
        $this->assertArrayHasKey('title', $controls['controls']);
        $this->assertNull(app(Renderer::class)->getWidgetControls('nope'));
    }

    public function test_sections_get_10px_vertical_margin_by_default_and_it_can_change(): void
    {
        $this->add('section');
        $this->assertStringContainsString('margin: 10px 0 10px 0', $this->render());

        $custom = $this->add('section', ['margin_top' => '40', 'margin_bottom' => '0px']);
        $this->assertStringContainsString('margin: 40px 0 0px 0', $this->render(), 'a bare number means px');
        $this->assertSame('40', $custom->settings['margin_top']);
    }

    public function test_padding_and_margin_from_the_dimensions_control_reach_the_page(): void
    {
        $el = $this->add('video', ['video_type' => 'youtube', 'video_url' => 'https://www.youtube.com/watch?v=aGhRCf0Hk5Y']);
        // saved before units were added automatically: "15" must still work
        $el->update(['styles' => ['marginTop' => '15', 'paddingLeft' => '8px', 'line_height' => '1.5']]);

        $html = $this->render();

        $this->assertMatchesRegularExpression('/data-element-id="' . $el->id . '"[^>]*style="[^"]*margin-top: 15px;[^"]*padding-left: 8px;/', $html);
        $this->assertStringContainsString('line-height: 1.5;', $html, 'line-height keeps its unitless multiplier');
    }

    // ------------------------------------------------------------- WidgetManager

    public function test_widget_manager_categories_and_lookup(): void
    {
        $wm = app(WidgetManager::class);

        $this->assertTrue($wm->hasWidget('heading'));
        $this->assertFalse($wm->hasWidget('nope'));
        $this->assertSame($wm->getWidgetCount(), count($wm->getWidgets()));
        foreach ($wm->getCategories() as $category => $types) {
            foreach ($types as $type) {
                $this->assertTrue($wm->hasWidget($type), "{$category} lists unregistered {$type}");
            }
        }

        $fresh = new WidgetManager();
        $fresh->register($wm->getWidget('heading'));
        $fresh->removeWidget('heading');
        $this->assertFalse($fresh->hasWidget('heading'));
    }

    // ------------------------------------------------------------ Global widgets

    public function test_global_widget_renders_and_syncs_into_an_element(): void
    {
        $this->actingAs($this->user);
        $service = app(GlobalWidgetService::class);
        $gw = $service->create(['title' => 'CTA', 'type' => 'heading', 'settings' => ['title' => 'Compre já', 'tag' => 'h3']]);

        $this->assertSame($this->user->id, $gw->user_id);
        $this->assertStringContainsString('Compre já', $service->renderWidget($gw));

        $el = $this->add('heading', ['title' => 'antigo']);
        $synced = $service->syncToElement($gw, $el);
        $this->assertSame('Compre já', $synced->settings['title']);

        $service->delete($gw);
        $this->assertSoftDeleted($gw);
    }


    // ---------------------------------------------------------------------- Jobs

    public function test_autosave_job_creates_a_revision_with_a_snapshot(): void
    {
        $this->add('heading', ['title' => 'Snapshot']);

        (new AutoSaveRevisionJob($this->page, $this->user->id))->handle();

        $revision = Revision::where('page_id', $this->page->id)->latest('id')->first();
        $this->assertNotNull($revision);
        $this->assertSame('auto_save', $revision->type);
        $this->assertStringContainsString('Snapshot', json_encode($revision->elements));
    }

    public function test_clear_cache_job_touches_the_page(): void
    {
        $before = $this->page->updated_at;
        $this->travel(5)->seconds();

        (new ClearPageCacheJob($this->page))->handle();

        $this->assertTrue($this->page->fresh()->updated_at->gt($before));
    }

    public function test_import_html_job_creates_a_page(): void
    {
        $result = (new ImportHtmlJob('<h1>Importado</h1><p>Texto</p>', $this->user->id, 'Nome Padrão'))->handle();

        $page = Page::find($result['page_id']);
        // the document's own <title>/<h1> wins over the fallback name
        $this->assertSame('Importado', $page->title);
        $this->assertGreaterThan(0, $page->allElements()->count());

        $untitled = (new ImportHtmlJob('<p>Sem título</p>', $this->user->id, 'Nome Padrão'))->handle();
        $this->assertSame('Nome Padrão', Page::find($untitled['page_id'])->title);
    }
}
