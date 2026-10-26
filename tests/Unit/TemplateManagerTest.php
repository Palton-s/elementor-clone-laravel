<?php

namespace Tests\Unit;

use App\Models\Page;
use App\Models\User;
use App\Services\PageBuilder\Core\TemplateManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateManagerTest extends TestCase
{
    use RefreshDatabase;

    protected TemplateManager $manager;
    protected User $user;
    protected Page $page;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = new TemplateManager();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->page = Page::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_returns_all_templates(): void
    {
        $templates = $this->manager->all();

        $this->assertIsArray($templates);
        $this->assertArrayHasKey('blank', $templates);
        $this->assertArrayHasKey('landing', $templates);
        $this->assertArrayHasKey('about', $templates);
        $this->assertArrayHasKey('contact', $templates);
        $this->assertArrayHasKey('showcase', $templates);
    }

    public function test_lists_templates_with_metadata(): void
    {
        $list = $this->manager->list();

        $this->assertIsArray($list);
        foreach ($list as $key => $tmpl) {
            $this->assertArrayHasKey('name', $tmpl);
            $this->assertArrayHasKey('description', $tmpl);
            $this->assertArrayNotHasKey('elements', $tmpl);
            $this->assertArrayNotHasKey('settings', $tmpl);
        }
    }

    public function test_checks_if_template_exists(): void
    {
        $this->assertTrue($this->manager->has('blank'));
        $this->assertTrue($this->manager->has('landing'));
        $this->assertTrue($this->manager->has('showcase'));
        $this->assertFalse($this->manager->has('nonexistent'));
    }

    public function test_gets_template_by_key(): void
    {
        $blank = $this->manager->get('blank');

        $this->assertIsArray($blank);
        $this->assertEquals('Página em branco', $blank['name']);
        $this->assertArrayHasKey('settings', $blank);
        $this->assertArrayHasKey('elements', $blank);
        $this->assertEmpty($blank['elements']);
    }

    public function test_returns_null_for_nonexistent_template(): void
    {
        $this->assertNull($this->manager->get('nonexistent'));
    }

    public function test_apply_template_creates_elements(): void
    {
        $page = $this->manager->apply($this->page, 'landing');

        $this->assertInstanceOf(Page::class, $page);
        $this->assertGreaterThan(0, $page->elements()->count());

        $sections = $page->elements()->whereNull('parent_id')->get();
        $this->assertGreaterThanOrEqual(1, $sections->count());
    }

    public function test_apply_template_merges_settings(): void
    {
        $this->page->settings = ['custom_setting' => 'value'];
        $this->page->save();

        $page = $this->manager->apply($this->page, 'blank');

        $this->assertEquals('value', $page->settings['custom_setting']);
        $this->assertEquals('1140px', $page->settings['container_width']);
    }

    public function test_apply_invalid_template_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->manager->apply($this->page, 'nonexistent');
    }

    public function test_import_to_page_does_nothing_for_invalid_template(): void
    {
        $this->manager->importToPage($this->page, 'nonexistent');
        $this->assertEquals(0, $this->page->elements()->count());
    }

    public function test_blank_template_has_no_elements(): void
    {
        $page = $this->manager->apply($this->page, 'blank');

        $this->assertEquals(0, $page->elements()->count());
    }

    public function test_showcase_template_has_complex_structure(): void
    {
        $template = $this->manager->get('showcase');

        $this->assertGreaterThan(5, count($template['elements']));
    }

    public function test_template_has_correct_landing_structure(): void
    {
        $template = $this->manager->get('landing');

        $this->assertCount(3, $template['elements']);
        $this->assertEquals('section', $template['elements'][0]['type']);
        $this->assertArrayHasKey('children', $template['elements'][0]);
        $this->assertEquals('section', $template['elements'][1]['type']);
        $this->assertEquals('section', $template['elements'][2]['type']);
    }

    public function test_lists_all_theme_templates(): void
    {
        $list = $this->manager->themeTemplateList();

        $expected = ['header', 'footer', 'single', 'archive', '404', 'search_results'];
        foreach ($expected as $type) {
            $this->assertArrayHasKey($type, $list);
            $this->assertArrayHasKey('name', $list[$type]);
            $this->assertArrayHasKey('description', $list[$type]);
        }
    }

    public function test_apply_theme_template_creates_elements_and_settings(): void
    {
        $this->manager->applyThemeTemplate($this->page, 'header');

        $this->assertGreaterThan(0, $this->page->elements()->count());
        $this->assertEquals('full', $this->page->settings['container_width']);
        $this->assertTrue($this->manager->hasThemeTemplate('header'));
        $this->assertFalse($this->manager->hasThemeTemplate('nonexistent'));
    }

    public function test_apply_invalid_theme_template_does_nothing(): void
    {
        $this->manager->applyThemeTemplate($this->page, 'nonexistent');

        $this->assertEquals(0, $this->page->elements()->count());

        $this->assertNull($this->manager->themeTemplate('nonexistent'));
    }



}
