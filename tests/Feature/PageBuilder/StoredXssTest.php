<?php

namespace Tests\Feature\PageBuilder;

use App\Models\Element;
use App\Models\Page;
use App\Models\User;
use App\Services\PageBuilder\Core\ElementManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Values go in through the real save endpoints and come out on the published page:
 * nothing an editor types may end up as executable markup for visitors.
 */
class StoredXssTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Page $page;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->page = Page::factory()->create(['user_id' => $this->user->id, 'status' => 'published']);
    }

    private function element(string $type): Element
    {
        return app(ElementManager::class)->create($this->page, $type, []);
    }

    private function publishedHtml(): string
    {
        return $this->actingAs($this->user)->get(route('page-builder.render', $this->page))->assertOk()->getContent();
    }

    /**
     * Parses the page like a browser would and looks for what actually executes: script elements
     * with the payload, on* attributes, and javascript: URLs. Payload text sitting harmlessly
     * inside an attribute value does not count.
     */
    private function assertNoInjectedScript(string $html): void
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        foreach ($dom->getElementsByTagName('script') as $script) {
            $this->assertStringNotContainsString('alert(', $script->textContent, 'injected <script> element');
        }
        foreach ((new \DOMXPath($dom))->query('//*[@*]') as $node) {
            foreach ($node->attributes as $attr) {
                $name = strtolower($attr->name);
                $this->assertFalse(str_starts_with($name, 'on'), "injected event handler {$name}=\"{$attr->value}\" on <{$node->nodeName}>");
                if (in_array($name, ['href', 'src', 'action', 'formaction'], true)) {
                    $this->assertDoesNotMatchRegularExpression('/^\s*javascript:/i', $attr->value, "javascript: URL in {$name}");
                }
            }
        }
    }

    public static function stylePayloads(): array
    {
        return [
            'quote breaks style attribute' => ['section', 'padding_top', '10px" onmouseover="alert(1)'],
            'single quote variant' => ['section', 'border_radius', "0px' onmouseover='alert(1)"],
            'closing tag' => ['heading', 'color', '#fff"><script>alert(1)</script>'],
            'column background' => ['column', 'background_color', 'red" onmouseover="alert(1)'],
        ];
    }

    #[DataProvider('stylePayloads')]
    public function test_style_values_cannot_break_out_of_attributes(string $type, string $key, string $payload): void
    {
        $el = $this->element($type);

        $this->actingAs($this->user)->putJson(route('page-builder.elements.styles', $el), ['styles' => [$key => $payload]])->assertOk();

        $this->assertNoInjectedScript($this->publishedHtml());
    }

    public static function settingPayloads(): array
    {
        return [
            'img onerror in rich text' => ['text', 'content', '<p>Oi</p><img src=x onerror=alert(1)>'],
            'script in rich text' => ['text', 'content', '<p>Oi</p><script>alert(1)</script>'],
            'javascript link in rich text' => ['text', 'content', '<a href="javascript:alert(1)">x</a>'],
            'svg onload in rich text' => ['text', 'content', '<div onmouseover="alert(1)">x</div>'],
            'css classes quote' => ['heading', 'css_classes', 'a" onmouseover="alert(1)'],
            'button link javascript' => ['button', 'link', 'javascript:alert(1)'],
            'button link quote' => ['button', 'link', 'https://x.com/" onmouseover="alert(1)'],
            'heading title' => ['heading', 'title', '<script>alert(1)</script>'],
            'section padding via settings' => ['section', 'padding_top', '10px" onmouseover="alert(1)'],
        ];
    }

    #[DataProvider('settingPayloads')]
    public function test_setting_values_cannot_inject_markup(string $type, string $key, string $payload): void
    {
        $el = $this->element($type);

        $this->actingAs($this->user)->putJson(route('page-builder.elements.settings', $el), ['settings' => [$key => $payload]])->assertOk();

        $this->assertNoInjectedScript($this->publishedHtml());
    }

    public function test_device_overrides_are_sanitized_too(): void
    {
        $el = $this->element('section');

        $this->actingAs($this->user)->putJson(route('page-builder.elements.styles', $el), [
            'styles' => ['padding_top' => '10px" onmouseover="alert(1)'], 'device' => 'tablet',
        ])->assertOk();

        $this->assertNoInjectedScript($this->publishedHtml());
    }

    public function test_rich_text_keeps_safe_formatting(): void
    {
        $el = $this->element('text');
        $safe = '<p>Olá <strong>mundo</strong> <a href="https://exemplo.com">link</a></p>';

        $this->actingAs($this->user)->putJson(route('page-builder.elements.settings', $el), ['settings' => ['content' => $safe]])->assertOk();

        $html = $this->publishedHtml();
        $this->assertStringContainsString('<strong>mundo</strong>', $html);
        $this->assertStringContainsString('href="https://exemplo.com"', $html);
    }

    public function test_css_values_with_quotes_keep_working(): void
    {
        $el = $this->element('heading');

        $this->actingAs($this->user)->putJson(route('page-builder.elements.styles', $el), [
            'styles' => ['font_family' => '"Open Sans", sans-serif'],
        ])->assertOk();

        $this->assertStringContainsString('Open Sans', $this->publishedHtml());
    }
}
