<?php

namespace Tests\Unit\Widgets;

use App\Services\PageBuilder\Core\WidgetManager;
use App\Services\PageBuilder\Widgets\BaseWidget;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Smoke-tests every registered widget: it must render (public and editor) with its defaults,
 * with partially saved settings, and must not echo script payloads put in text settings.
 */
class AllWidgetsRenderTest extends TestCase
{
    private const XSS = '<script>alert(1)</script>"><img src=x onerror=alert(2)>';

    /** Settings that legitimately hold HTML (sanitized when saved, not when rendered). */
    private const HTML_KEYS = ['content', 'html', 'description', 'text', 'custom_css', 'custom_js', 'code', 'formula', 'answer', 'body'];

    /**
     * Read straight from the config file: data providers run while the suite loads, before
     * (and without) a booted application.
     */
    public static function widgetTypes(): array
    {
        $config = require __DIR__ . '/../../../config/page-builder.php';

        return collect($config['widgets'] ?? [])
            ->mapWithKeys(fn ($class) => [class_basename($class) => [$class]])
            ->all();
    }

    private function widget(string $class): BaseWidget
    {
        $widget = app($class);
        $this->assertInstanceOf(BaseWidget::class, $widget);
        $this->assertTrue(app(WidgetManager::class)->hasWidget($widget->getType()), "{$class} is configured but not registered");

        return $widget;
    }

    public function test_every_configured_widget_is_registered(): void
    {
        $this->assertGreaterThanOrEqual(30, count(app(WidgetManager::class)->getWidgets()));
    }

    #[DataProvider('widgetTypes')]
    public function test_renders_with_default_settings(string $type): void
    {
        $widget = $this->widget($type);
        $defaults = $widget->getDefaultSettings();

        $html = $widget->render($defaults, ['children' => '<p>child</p>'], []);
        $editor = $widget->renderEditor($defaults, ['children' => '<p>child</p>'], []);

        $this->assertIsString($html);
        $this->assertIsString($editor);
        $this->assertNotSame('', trim($editor), "{$type} editor preview must not be empty");
    }

    #[DataProvider('widgetTypes')]
    public function test_renders_with_empty_settings(string $type): void
    {
        // Elements saved by the API/import may carry no settings at all
        $widget = $this->widget($type);

        $this->assertIsString($widget->render([], [], []));
        $this->assertIsString($widget->renderEditor([], [], []));
    }

    #[DataProvider('widgetTypes')]
    public function test_container_widgets_output_their_children(string $type): void
    {
        $widget = $this->widget($type);
        if (!$widget->isContainer()) {
            $this->assertFalse($widget->isContainer());
            return;
        }

        $html = $widget->render($widget->getDefaultSettings(), ['children' => '<p id="kid">child</p>'], []);
        $this->assertStringContainsString('<p id="kid">child</p>', $html);
    }

    #[DataProvider('widgetTypes')]
    public function test_plain_text_settings_are_escaped(string $type): void
    {
        $widget = $this->widget($type);
        $settings = $widget->getDefaultSettings();

        $poisoned = [];
        foreach ($settings as $key => $value) {
            if (is_string($value) && !in_array($key, self::HTML_KEYS, true) && !$this->isEnumLike($widget, $key)) {
                $settings[$key] = self::XSS;
                $poisoned[] = $key;
            }
        }
        if ($poisoned === []) {
            $this->assertSame([], $poisoned);
            return;
        }

        $html = $widget->render($settings, [], []);

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html, "{$type} echoes raw script from: " . implode(', ', $poisoned));
        $this->assertDoesNotMatchRegularExpression('/<img[^>]+onerror=alert/i', $html, "{$type} echoes an injected tag from: " . implode(', ', $poisoned));
    }

    #[DataProvider('widgetTypes')]
    public function test_numeric_settings_tolerate_blank_or_typed_values(string $type): void
    {
        // A field the user cleared arrives as '' and a typo as text; the page must still render
        $widget = $this->widget($type);
        foreach (['' => 'blank', 'abc' => 'text'] as $value => $label) {
            $settings = $widget->getDefaultSettings();
            foreach ($settings as $key => $default) {
                if (is_int($default) || is_float($default) || (is_string($default) && is_numeric($default))) {
                    $settings[$key] = $value;
                }
            }
            $this->assertIsString($widget->render($settings, [], []), "{$type} with {$label} numbers");
            $this->assertIsString($widget->renderEditor($settings, [], []), "{$type} editor with {$label} numbers");
        }
    }

    #[DataProvider('widgetTypes')]
    public function test_controls_describe_known_types(string $type): void
    {
        $known = ['text', 'number', 'textarea', 'select', 'color', 'boolean', 'switcher', 'url', 'image', 'video', 'wysiwyg',
            'icon', 'gallery', 'repeater', 'typography', 'background', 'border', 'box_shadow', 'dimensions', 'hover',
            'custom_css', 'animation', 'visibility', 'gradient', 'scroll_animation', 'text_shadow', 'text_stroke',
            'column_width', 'code', 'date', 'datetime', 'slider', 'media', 'json', 'math', 'file'];

        foreach ($this->widget($type)->getControls() as $key => $control) {
            $this->assertIsArray($control, "{$type}.{$key}");
            $this->assertArrayHasKey('type', $control, "{$type}.{$key} has no type");
            $this->assertContains($control['type'], $known, "{$type}.{$key} uses unknown control type '{$control['type']}'");
        }
    }

    /** Select options and CSS-ish values are not free text. */
    private function isEnumLike(BaseWidget $widget, string $key): bool
    {
        $control = $widget->getControls()[$key] ?? null;
        if ($control && in_array($control['type'] ?? '', ['select', 'color', 'number', 'icon', 'url', 'image', 'video'], true)) {
            return true;
        }

        // CSS / class values: cleaned when saved (see StoredXssTest), not escaped again by every widget
        return (bool) preg_match('/(color|align|width|height|size|radius|padding|margin|gap|spacing|position|type|style|tag|layout|target|effect|weight|transform|direction|ratio|speed|duration|delay|easing|shadow|border|opacity|family|icon|url|link|src|image|video|date|time|lat|lng|zoom|renderer|loop|mode|class|animation|index|wrap|justify|flex|overflow|display|cursor|filter|blend|visibility)/', $key);
    }
}
