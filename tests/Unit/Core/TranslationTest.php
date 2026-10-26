<?php

namespace Tests\Unit\Core;

use App\Services\PageBuilder\Core\ControlTranslator;
use App\Services\PageBuilder\Core\Renderer;
use App\Services\PageBuilder\Core\WidgetManager;
use Tests\TestCase;

/**
 * Everything the editor shows for widgets must be in Portuguese: a new widget, label or
 * option without an entry in lang/pt_BR/page_builder.php fails here with its name.
 */
class TranslationTest extends TestCase
{
    public function test_app_runs_in_portuguese(): void
    {
        $this->assertSame('pt_BR', app()->getLocale());
        $this->assertSame('O campo título é obrigatório.', validator(['title' => ''], ['title' => 'required'])->errors()->first());
    }

    public function test_every_widget_name_label_section_and_option_is_translated(): void
    {
        $dict = trans('page_builder');
        $missing = [];

        foreach (app(WidgetManager::class)->getWidgets() as $widget) {
            if (!isset($dict['widgets'][$widget->getType()])) {
                $missing[] = "widget {$widget->getType()}";
            }
            $walk = function (array $controls, string $path) use (&$walk, &$missing, $dict) {
                foreach ($controls as $key => $control) {
                    if (!is_array($control)) {
                        continue;
                    }
                    if (isset($control['label']) && !isset($dict['labels'][$control['label']])) {
                        $missing[] = "label \"{$control['label']}\" ({$path}.{$key})";
                    }
                    if (isset($control['section']) && !isset($dict['sections'][$control['section']])) {
                        $missing[] = "section \"{$control['section']}\" ({$path}.{$key})";
                    }
                    foreach ($control['options'] ?? [] as $optKey => $optValue) {
                        $text = (string) $optValue;
                        // numbers, heading tags, ratios and icon classes read the same in any language
                        if (is_numeric($text) || preg_match('/^(h[1-6]|\d+:\d+|fa[srb]? .+)$/', $text)) {
                            continue;
                        }
                        if (!isset($dict['options'][$text])) {
                            $missing[] = "option \"{$text}\" ({$path}.{$key})";
                        }
                    }
                    if (isset($control['fields'])) {
                        $walk($control['fields'], "{$path}.{$key}");
                    }
                }
            };
            $walk($widget->getControls(), $widget->getType());
        }

        $this->assertSame([], array_values(array_unique($missing)), "Sem tradução em lang/pt_BR/page_builder.php:\n" . implode("\n", array_unique($missing)));
    }

    public function test_editor_receives_translated_controls_but_original_values(): void
    {
        $controls = app(Renderer::class)->getWidgetControls('heading');

        $this->assertSame('Título', $controls['label']);
        $this->assertSame('Alinhamento', $controls['controls']['alignment']['label']);
        // values stay as stored (CSS), texts are separate
        $this->assertSame(['left', 'center', 'right', 'justify'], $controls['controls']['alignment']['options']);
        $this->assertSame('Esquerda', $controls['controls']['alignment']['option_labels']['left']);
    }

    public function test_value_to_text_options_keep_their_values(): void
    {
        $controls = ControlTranslator::controls(['dir' => ['type' => 'select', 'label' => 'Direction', 'options' => ['row' => 'Horizontal', 'column' => 'Vertical']]]);

        $this->assertSame(['row' => 'Horizontal', 'column' => 'Vertical'], $controls['dir']['option_labels']);
        $this->assertSame('Direção', $controls['dir']['label']);
    }

    public function test_new_elements_get_portuguese_names_and_defaults(): void
    {
        $wm = app(WidgetManager::class);

        $this->assertSame('Espaçador', $wm->getWidget('spacer')->getLabel());
        $this->assertSame('Galeria', $wm->getWidget('gallery')->getLabel());
        $this->assertSame('Clique aqui', $wm->getWidget('button')->getDefaultSettings()['text']);
        $this->assertSame('Seu título aqui', $wm->getWidget('heading')->getDefaultSettings()['title']);
    }

    public function test_widget_defaults_have_no_english_placeholder_text(): void
    {
        $english = '/\b(Your|Click Here|Lorem ipsum|Send Message|Thank you|Tab \d|Section \d|Content for|Projects Completed|Professional|Choose)\b/';
        foreach (app(WidgetManager::class)->getWidgets() as $widget) {
            $json = json_encode($widget->getDefaultSettings(), JSON_UNESCAPED_UNICODE);
            $this->assertDoesNotMatchRegularExpression($english, $json, "{$widget->getType()} default settings still in English");
        }
    }
}
