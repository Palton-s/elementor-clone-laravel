<?php

namespace Tests\Unit\Security;

use App\Services\PageBuilder\Security\HtmlSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public static function dangerousRichText(): array
    {
        return [
            'script tag' => ['<p>a</p><script>alert(1)</script>'],
            'img onerror' => ['<img src="x.png" onerror="alert(1)">'],
            'slash separated handler' => ['<img/src="x.png"/onerror=alert(1)>'],
            'unquoted handler' => ['<div onclick=alert(1)>x</div>'],
            'uppercase handler' => ['<div ONMOUSEOVER="alert(1)">x</div>'],
            'javascript href' => ['<a href="javascript:alert(1)">x</a>'],
            'entity encoded javascript' => ['<a href="&#106;avascript:alert(1)">x</a>'],
            'whitespace in scheme' => ['<a href=" java	script:alert(1)">x</a>'],
            'svg payload' => ['<svg onload="alert(1)"><circle/></svg>'],
            'iframe from unknown host' => ['<iframe src="https://evil.example/x"></iframe>'],
            'iframe srcdoc' => ['<iframe srcdoc="<script>alert(1)</script>"></iframe>'],
            'object embed' => ['<object data="x.swf"></object><embed src="x.swf">'],
            'style expression' => ['<div style="width: expression(alert(1))">x</div>'],
            'form' => ['<form action="https://evil.example"><input name=a></form>'],
        ];
    }

    #[DataProvider('dangerousRichText')]
    public function test_rich_text_removes_executable_markup(string $html): void
    {
        $clean = HtmlSanitizer::sanitizeRichText($html);

        $this->assertDoesNotMatchRegularExpression('/<script/i', $clean);
        $this->assertDoesNotMatchRegularExpression('/\son\w+\s*=/i', $clean);
        $this->assertDoesNotMatchRegularExpression('/javascript\s*:/i', $clean);
        $this->assertDoesNotMatchRegularExpression('/<(svg|object|embed|form)\b/i', $clean);
        $this->assertStringNotContainsString('evil.example', $clean);
        $this->assertStringNotContainsString('expression(', $clean);
    }

    public function test_rich_text_keeps_formatting_links_media_and_tables(): void
    {
        $html = '<h2 class="titulo">Título</h2><p style="color:#ff0000">Olá <strong>forte</strong> <em>itálico</em> <u>sub</u> '
            . '<a href="https://exemplo.com" target="_blank">link</a> <a href="mailto:a@b.com">mail</a></p>'
            . '<ul><li>um</li></ul><img src="/storage/a.png" alt="A"><table><tr><td colspan="2">c</td></tr></table>'
            . '<blockquote>q</blockquote><pre><code>x = 1</code></pre>';

        $clean = HtmlSanitizer::sanitizeRichText($html);

        foreach (['<h2 class="titulo">', 'color:#ff0000', '<strong>forte</strong>', '<em>itálico</em>', '<u>sub</u>',
            'href="https://exemplo.com"', 'target="_blank"', 'href="mailto:a@b.com"', '<li>um</li>', 'src="/storage/a.png"',
            'colspan="2"', '<blockquote>', '<code>x = 1</code>'] as $expected) {
            $this->assertStringContainsString($expected, $clean);
        }
    }

    public function test_rich_text_keeps_youtube_and_vimeo_embeds(): void
    {
        $yt = HtmlSanitizer::sanitizeRichText('<iframe src="https://www.youtube.com/embed/abc123" width="560" height="315"></iframe>');
        $vm = HtmlSanitizer::sanitizeRichText('<iframe src="https://player.vimeo.com/video/42"></iframe>');

        $this->assertStringContainsString('src="https://www.youtube.com/embed/abc123"', $yt);
        $this->assertStringContainsString('src="https://player.vimeo.com/video/42"', $vm);
    }

    public function test_rich_text_keeps_html5_video(): void
    {
        $clean = HtmlSanitizer::sanitizeRichText('<video controls src="/storage/v.mp4"><source src="/storage/v.webm" type="video/webm"></video>');

        $this->assertStringContainsString('<video', $clean);
        $this->assertStringContainsString('/storage/v.mp4', $clean);
    }

    public function test_rich_text_leaves_plain_text_alone(): void
    {
        $this->assertSame('', HtmlSanitizer::sanitizeRichText(''));
        $this->assertSame('Apenas texto', HtmlSanitizer::sanitizeRichText('Apenas texto'));
    }

    public function test_legacy_sanitize_strips_handlers_including_slash_variant(): void
    {
        $clean = HtmlSanitizer::sanitize('<img/src=x/onerror=alert(1)><a href="x" onclick="y()">a</a>');

        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
    }

    public function test_legacy_sanitize_does_not_touch_text_that_looks_like_a_handler(): void
    {
        $this->assertStringContainsString('onboarding = fácil', HtmlSanitizer::sanitize('<p>Nosso onboarding = fácil</p>'));
    }

    public function test_legacy_sanitize_neutralizes_dangerous_urls(): void
    {
        $clean = HtmlSanitizer::sanitize('<a href="javascript:alert(1)">x</a><img src="data:image/svg+xml;base64,AAA"><iframe srcdoc="x"></iframe>');

        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringNotContainsString('data:image/svg', $clean);
        $this->assertStringNotContainsString('srcdoc', $clean);
    }

    public static function urls(): array
    {
        return [
            'https' => ['https://exemplo.com/a?b=1', 'https://exemplo.com/a?b=1'],
            'relative' => ['/p/sobre', '/p/sobre'],
            'anchor' => ['#contato', '#contato'],
            'mailto' => ['mailto:a@b.com', 'mailto:a@b.com'],
            'javascript' => ['javascript:alert(1)', ''],
            'javascript mixed case and spaces' => ['  JaVaScRiPt:alert(1)', ''],
            'vbscript' => ['vbscript:msgbox(1)', ''],
            'data html' => ['data:text/html,<script>alert(1)</script>', ''],
            'quote break out' => ['https://x.com/" onmouseover="a', 'https://x.com/%22%20onmouseover=%22a'],
            'angle brackets' => ['https://x.com/<b>', 'https://x.com/%3Cb%3E'],
            'plain word (link_target)' => ['_blank', '_blank'],
        ];
    }

    #[DataProvider('urls')]
    public function test_url_values(string $input, string $expected): void
    {
        $this->assertSame($expected, HtmlSanitizer::sanitizeUrlValue($input));
    }

    public function test_dangerous_url_detection_sees_through_obfuscation(): void
    {
        $this->assertTrue(HtmlSanitizer::isDangerousUrl('&#x6A;avascript:alert(1)'));
        $this->assertTrue(HtmlSanitizer::isDangerousUrl("java\nscript:alert(1)"));
        $this->assertTrue(HtmlSanitizer::isDangerousUrl('data:image/svg+xml,<svg onload=alert(1)>'));
        $this->assertFalse(HtmlSanitizer::isDangerousUrl('data:image/png;base64,AAAA'));
        $this->assertFalse(HtmlSanitizer::isDangerousUrl('https://javascript.info'));
    }

    public function test_css_values_cannot_close_the_style_attribute(): void
    {
        $this->assertSame("10px' onmouseover='alert(1)", HtmlSanitizer::sanitizeCssValue('10px" onmouseover="alert(1)'));
        $this->assertSame("'Open Sans', sans-serif", HtmlSanitizer::sanitizeCssValue('"Open Sans", sans-serif'));
        $this->assertSame('#fffscriptalert(1)/script', HtmlSanitizer::sanitizeCssValue('#fff<script>alert(1)</script>'));
    }

    public function test_identifiers_keep_only_selector_characters(): void
    {
        $this->assertSame('btn btn-primary my_class md:flex', HtmlSanitizer::sanitizeIdentifiers('btn btn-primary my_class md:flex'));
        $this->assertSame('a onmouseoveralert1', HtmlSanitizer::sanitizeIdentifiers('a" onmouseover="alert(1)'));
    }

    public function test_custom_css_and_js_filters(): void
    {
        $css = HtmlSanitizer::sanitizeCustomCss('color: red; width: expression(alert(1)); background: url(javascript:alert(1)); -moz-binding: url(x)</style><script>');
        $this->assertStringNotContainsString('expression(', $css);
        $this->assertStringNotContainsString('javascript:', $css);
        $this->assertStringNotContainsString('-moz-binding:', $css);
        $this->assertStringNotContainsString('<', $css);
        $this->assertStringContainsString('color: red', $css);
        $this->assertSame('', HtmlSanitizer::sanitizeCustomCss(null));

        $js = HtmlSanitizer::sanitizeCustomJs('eval("x"); document.write("y"); console.log(1);');
        $this->assertStringNotContainsString('eval(', $js);
        $this->assertStringNotContainsString('document.write(', $js);
        $this->assertStringContainsString('console.log(1)', $js);
    }
}
