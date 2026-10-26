<?php

namespace Tests\Feature\PageBuilder;

use App\Models\Element;
use App\Models\Page;
use App\Models\User;
use App\Services\PageBuilder\Core\ElementManager;
use App\Services\PageBuilder\Core\ResponsiveStyleCompiler;
use App\Services\PageBuilder\Core\PageBuilderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponsiveStylesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Page $page;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->page = Page::factory()->create(['user_id' => $this->user->id]);
    }

    private function heading(): Element
    {
        return app(ElementManager::class)->create($this->page, 'heading', ['title' => 'Olá', 'color' => '#111111', 'alignment' => 'left']);
    }

    private function render(): string
    {
        return app(PageBuilderService::class)->renderPage($this->page->fresh(), ['with_container' => false]);
    }

    public function test_device_values_are_saved_apart_from_desktop(): void
    {
        $el = $this->heading();

        $this->actingAs($this->user)->putJson(route('page-builder.elements.settings', $el), [
            'settings' => ['color' => '#ff0000'], 'device' => 'tablet',
        ])->assertOk();

        $el->refresh();
        $this->assertSame('#111111', $el->settings['color'], 'desktop must not change');
        $this->assertSame('#ff0000', $el->responsive_settings['tablet']['settings']['color']);

        // mobile inherits tablet, desktop stays
        $this->assertSame('#ff0000', $el->valuesForDevice('mobile')[0]['color']);
        $this->assertSame('#111111', $el->valuesForDevice('desktop')[0]['color']);

        // null resets the override
        $this->actingAs($this->user)->putJson(route('page-builder.elements.settings', $el), [
            'settings' => ['color' => null], 'device' => 'tablet',
        ])->assertOk();
        $this->assertSame('#111111', $el->fresh()->valuesForDevice('tablet')[0]['color']);
    }

    public function test_published_page_gets_media_queries_for_device_styles(): void
    {
        $el = $this->heading();
        $el->responsive_settings = [
            'tablet' => ['settings' => ['color' => '#ff0000']],
            'mobile' => ['settings' => ['alignment' => 'center']],
        ];
        $el->save();

        $html = $this->render();

        // desktop stays inline, overrides go to media queries targeting the marked tag
        $this->assertStringContainsString('color: #111111', $html);
        $this->assertMatchesRegularExpression('/<h2 data-pbr="e' . $el->id . '-\d+"/', $html);
        $this->assertMatchesRegularExpression('/@media \(max-width: 1024px\)\{\[data-pbr="e' . $el->id . '-\d+"\]\{color:#ff0000 !important;\}\}/', $html);
        // phones get their own alignment and keep the tablet colour (cascade)
        $this->assertMatchesRegularExpression('/@media \(max-width: 767px\)\{[^}]*text-align:center !important;/', $html);
        $this->assertStringNotContainsString('pb-rv', $html, 'style-only changes must not duplicate markup');
    }

    public function test_markup_changes_fall_back_to_per_device_variants(): void
    {
        $el = $this->heading();
        $el->responsive_settings = ['mobile' => ['settings' => ['tag' => 'h4']]];
        $el->save();

        $html = $this->render();

        $this->assertStringContainsString('data-pbrv="' . $el->id . '-desktop"', $html);
        $this->assertStringContainsString('data-pbrv="' . $el->id . '-mobile"', $html);
        $this->assertStringContainsString('<h4', $html);
    }

    public function test_column_width_per_device_overrides_the_default_stacking(): void
    {
        $manager = app(ElementManager::class);
        $section = $manager->create($this->page, 'section', []);
        $col = $manager->create($this->page, 'column', ['column_width' => 33], $section->id);
        $col->responsive_settings = ['mobile' => ['settings' => ['column_width' => 50]]];
        $col->save();

        $html = $this->render();

        $this->assertStringContainsString('flex: 0 1 33%', $html);
        $this->assertMatchesRegularExpression('/@media \(max-width: 767px\)\{\.pb-element\[data-element-id="' . $col->id . '"\]\{[^}]*flex:0 1 50% !important;/', $html);
    }

    public function test_legacy_suffix_values_still_apply(): void
    {
        $el = $this->heading();
        $el->settings = array_merge($el->settings, ['margin_bottom_tablet' => '4px']);
        $el->save();

        $this->assertSame('4px', $el->fresh()->valuesForDevice('tablet')[0]['margin_bottom']);
    }

    public function test_mobile_restates_values_tablet_changed(): void
    {
        $rules = (new ResponsiveStyleCompiler())->deviceRules(
            ['color' => 'black', 'margin' => '0'],
            ['tablet' => ['color' => 'red', 'margin' => '0'], 'mobile' => ['color' => 'black', 'margin' => '0']],
            ['tablet', 'mobile']
        );

        $this->assertSame(['color' => 'red'], $rules['tablet']);
        $this->assertSame(['color' => 'black'], $rules['mobile']);
    }

    public function test_editor_render_endpoint_uses_device_values(): void
    {
        $el = $this->heading();
        $el->responsive_settings = ['tablet' => ['settings' => ['color' => '#00ff00']]];
        $el->save();

        $this->actingAs($this->user)->get(route('page-builder.elements.render', $el) . '?device=tablet')
            ->assertOk()->assertSee('#00ff00', false);
        $this->actingAs($this->user)->get(route('page-builder.elements.render', $el))
            ->assertOk()->assertDontSee('#00ff00', false);
    }
}
