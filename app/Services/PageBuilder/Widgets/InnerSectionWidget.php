<?php

namespace App\Services\PageBuilder\Widgets;

class InnerSectionWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'inner_section';
        $this->label = 'Inner Section';
        $this->icon = 'inner-section-icon';
        $this->categories = ['basic', 'layout'];
        $this->keywords = ['inner', 'section', 'columns', 'nested', 'grid'];
        $this->container = true;

        $this->defaultSettings = [
            'columns' => 2,
            'column_gap' => '20px',
            'column_direction' => 'row',
            'padding_top' => '0px',
            'padding_bottom' => '0px',
            'padding_left' => '0px',
            'padding_right' => '0px',
            'background_color' => 'transparent',
            'border_radius' => '0px',
            'css_classes' => '',
        ];

        $this->controls = [
            'columns' => ['type' => 'select', 'label' => 'Columns', 'options' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6']],
            'column_gap' => ['type' => 'text', 'label' => 'Column Gap', 'default' => '20px'],
            'column_direction' => ['type' => 'select', 'label' => 'Direction', 'options' => ['row' => 'Horizontal', 'column' => 'Vertical']],
            'background_color' => ['type' => 'color', 'label' => 'Background Color', 'tab' => 'style'],
            'border_radius' => ['type' => 'text', 'label' => 'Border Radius', 'tab' => 'style'],
            'padding_top' => ['type' => 'text', 'label' => 'Padding Top', 'tab' => 'advanced'],
            'padding_bottom' => ['type' => 'text', 'label' => 'Padding Bottom', 'tab' => 'advanced'],
            'padding_left' => ['type' => 'text', 'label' => 'Padding Left', 'tab' => 'advanced'],
            'padding_right' => ['type' => 'text', 'label' => 'Padding Right', 'tab' => 'advanced'],
            'css_classes' => ['type' => 'text', 'label' => 'CSS Classes', 'tab' => 'advanced'],
        ];
    }

    public function render(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $children = $content['children'] ?? '';
        $columns = (int) ($settings['columns'] ?? 2);
        $gap = $settings['column_gap'] ?? '20px';
        $direction = $settings['column_direction'] ?? 'row';
        $bgColor = $settings['background_color'] ?? 'transparent';
        $borderRadius = $settings['border_radius'] ?? '0px';
        $pt = $settings['padding_top'] ?? '0px';
        $pb = $settings['padding_bottom'] ?? '0px';
        $pl = $settings['padding_left'] ?? '0px';
        $pr = $settings['padding_right'] ?? '0px';
        $cssClasses = $settings['css_classes'] ?? '';

        $style = "display:flex;flex-direction:{$direction};gap:{$gap};padding:{$pt} {$pr} {$pb} {$pl};";
        if ($bgColor !== 'transparent') $style .= "background-color:{$bgColor};";
        if ($borderRadius !== '0px') $style .= "border-radius:{$borderRadius};";

        $class = "pb-inner-section {$cssClasses}";

        return <<<HTML
<div class="{$class}" style="{$style}">
    {$children}
</div>
HTML;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $children = $content['children'] ?? '';
        $columns = (int) ($settings['columns'] ?? 2);
        $gap = $settings['column_gap'] ?? '20px';
        $direction = $settings['column_direction'] ?? 'row';
        $bgColor = $settings['background_color'] ?? 'transparent';
        $pt = $settings['padding_top'] ?? '0px';
        $pb = $settings['padding_bottom'] ?? '0px';
        $pl = $settings['padding_left'] ?? '0px';
        $pr = $settings['padding_right'] ?? '0px';

        $style = "display:flex;flex-direction:{$direction};gap:{$gap};padding:{$pt} {$pr} {$pb} {$pl};min-height:60px;border:1px dashed rgba(99,102,241,.3);border-radius:4px;";
        if ($bgColor !== 'transparent') $style .= "background-color:{$bgColor};";

        return <<<HTML
<div class="pb-inner-section-editor" style="{$style}">
    <div class="pb-inner-section-label" style="position:absolute;top:2px;left:4px;font-size:.55rem;color:var(--pb-text2);text-transform:uppercase;letter-spacing:.5px;">Seção Interna</div>
    {$children}
</div>
HTML;
    }
}
