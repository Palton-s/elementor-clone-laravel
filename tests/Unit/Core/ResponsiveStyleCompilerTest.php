<?php

namespace Tests\Unit\Core;

use App\Services\PageBuilder\Core\ResponsiveStyleCompiler;
use PHPUnit\Framework\TestCase;

class ResponsiveStyleCompilerTest extends TestCase
{
    private ResponsiveStyleCompiler $c;

    protected function setUp(): void
    {
        parent::setUp();
        $this->c = new ResponsiveStyleCompiler();
    }

    public function test_parse_style_reads_declarations_in_order(): void
    {
        $this->assertSame(
            ['color' => 'red', 'margin' => '0 auto', 'font-family' => "'Open Sans', sans-serif"],
            $this->c->parseStyle("color: red; margin:0 auto ;  font-family: 'Open Sans', sans-serif;")
        );
    }

    public function test_parse_style_keeps_semicolons_inside_urls(): void
    {
        $props = $this->c->parseStyle('background-image: url(data:image/png;base64,AAA=); color: blue');

        $this->assertSame('url(data:image/png;base64,AAA=)', $props['background-image']);
        $this->assertSame('blue', $props['color']);
    }

    public function test_parse_style_ignores_garbage(): void
    {
        $this->assertSame(['color' => 'red'], $this->c->parseStyle(';;color:red;:nothing;novalue:;bad prop:x'));
        $this->assertSame([], $this->c->parseStyle(''));
    }

    public function test_compile_returns_base_untouched_when_devices_match(): void
    {
        $html = '<div style="color: red">x</div>';

        $this->assertSame(['html' => $html, 'css' => ''], $this->c->compile($html, ['tablet' => $html, 'mobile' => $html], 'e1'));
    }

    public function test_compile_marks_changed_tags_and_emits_media_queries(): void
    {
        $base = '<div style="padding: 10px"><h2 style="color: red">x</h2></div>';
        $tablet = '<div style="padding: 10px"><h2 style="color: blue">x</h2></div>';

        $out = $this->c->compile($base, ['tablet' => $tablet, 'mobile' => $tablet], 'e9');

        $this->assertStringContainsString('<h2 data-pbr="e9-', $out['html']);
        $this->assertStringNotContainsString('<div data-pbr', $out['html'], 'unchanged tags are not marked');
        $this->assertMatchesRegularExpression('/@media \(max-width: 1024px\)\{\[data-pbr="e9-\d+"\]\{color:blue !important;\}\}/', $out['css']);
    }

    public function test_compile_gives_up_when_markup_differs(): void
    {
        $this->assertNull($this->c->compile('<h2>x</h2>', ['mobile' => '<h4>x</h4>'], 'e1'));
        $this->assertNull($this->c->compile('<p>a</p>', ['mobile' => '<p>b</p>'], 'e1'), 'text differs');
        $this->assertNull($this->c->compile('<p class="a">x</p>', ['mobile' => '<p class="b">x</p>'], 'e1'), 'attribute differs');
        $this->assertNull($this->c->compile('<p>x</p>', ['mobile' => '<p>x</p><p>y</p>'], 'e1'), 'extra element');
    }

    public function test_removed_properties_are_unset_on_the_device(): void
    {
        $rules = $this->c->deviceRules(['color' => 'red', 'border' => '1px solid'], ['tablet' => ['color' => 'red']], ['tablet']);

        $this->assertSame(['tablet' => ['border' => 'unset']], $rules);
    }

    public function test_device_only_on_mobile_leaves_tablet_alone(): void
    {
        $rules = $this->c->deviceRules(['color' => 'red'], ['mobile' => ['color' => 'green']], ['tablet', 'mobile']);

        $this->assertArrayNotHasKey('tablet', $rules);
        $this->assertSame(['color' => 'green'], $rules['mobile']);
    }

    public function test_declarations_add_important_once(): void
    {
        $this->assertSame('color:red !important;margin:0 !important;', $this->c->declarations(['color' => 'red !important', 'margin' => '0']));
    }

    public function test_media_queries_are_ordered_wide_to_narrow(): void
    {
        $css = $this->c->wrapMedia(['mobile' => ['.a{x:1}'], 'tablet' => ['.b{x:2}']]);

        $this->assertLessThan(strpos($css, '767px'), strpos($css, '1024px'));
        $this->assertSame('', $this->c->wrapMedia([]));
    }
}
