<?php

namespace App\Services\PageBuilder\Widgets;

class TextWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'text';
        $this->label = 'Text Editor';
        $this->icon = 'text-icon';
        $this->categories = ['basic', 'typography'];
        $this->keywords = ['text', 'paragraph', 'content', 'editor'];

        $this->defaultSettings = [
            'content' => '<p>Escreva aqui o seu texto. Clique para editar e use a barra de formatação para deixar em negrito, itálico ou criar listas e links.</p>',
            'alignment' => 'left',
            'color' => '#666666',
            'font_size' => '16px',
            'font_family' => '',
            'font_weight' => '400',
            'line_height' => '1.7',
            'margin_bottom' => '20px',
            'drop_cap' => false,
            'column_count' => 1,
            'column_gap' => 'normal',
        ];

        $this->controls = [
            'content' => ['type' => 'wysiwyg', 'label' => 'Content', 'required' => true],
            'alignment' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left', 'center', 'right', 'justify']],
            'drop_cap' => ['type' => 'boolean', 'label' => 'Drop Cap'],
            'color' => ['type' => 'color', 'label' => 'Color', 'tab' => 'style'],
            'font_size' => ['type' => 'number', 'label' => 'Font Size', 'min' => 8, 'max' => 200, 'tab' => 'style'],
            'font_family' => ['type' => 'text', 'label' => 'Font Family', 'tab' => 'style'],
            'line_height' => ['type' => 'number', 'label' => 'Line Height', 'min' => 0.5, 'max' => 5, 'tab' => 'style'],
            'typography' => ['type' => 'typography', 'label' => 'Typography', 'tab' => 'style'],
            'background' => ['type' => 'background', 'label' => 'Background', 'tab' => 'style'],
            'border' => ['type' => 'border', 'label' => 'Border', 'tab' => 'style'],
            'box_shadow' => ['type' => 'box_shadow', 'label' => 'Box Shadow', 'tab' => 'style'],
            'hover' => ['type' => 'hover', 'label' => 'Hover Effects', 'tab' => 'style'],
            'dimensions' => ['type' => 'dimensions', 'label' => 'Padding & Margin', 'tab' => 'advanced'],
            'z_index' => ['type' => 'number', 'label' => 'Z-Index', 'tab' => 'advanced'],
            'css_classes' => ['type' => 'text', 'label' => 'CSS Classes', 'tab' => 'advanced'],
            'css_id' => ['type' => 'text', 'label' => 'CSS ID', 'tab' => 'advanced'],
            'custom_css' => ['type' => 'custom_css', 'label' => 'Custom CSS', 'tab' => 'advanced'],
            'animation' => ['type' => 'animation', 'label' => 'Animation', 'tab' => 'advanced'],
            'visibility' => ['type' => 'visibility', 'label' => 'Responsive Visibility', 'tab' => 'advanced'],
        ];
    }

    public function render(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $text = $settings['content'];
        $alignment = $settings['alignment'];
        $color = $settings['color'] ?? '';
        $fontSize = $settings['font_size'] ?? '';
        $fontWeight = $settings['font_weight'] ?? '';
        $lineHeight = $settings['line_height'] ?? '';
        $fontFamily = $settings['font_family'] ?? '';
        $children = $content['children'] ?? '';

        $style = "text-align: {$this->safeCssValue($alignment)};";
        if ($color) {
            $style .= " color: {$this->safeCssValue($color)};";
        }
        if ($fontSize) {
            $style .= " font-size: {$this->safeCssValue($fontSize)};";
        }
        if ($fontWeight) {
            $style .= " font-weight: {$this->safeCssValue($fontWeight)};";
        }
        if ($lineHeight) {
            $style .= " line-height: {$this->safeCssValue($lineHeight)};";
        }

        if ($fontFamily) {
            $style .= " font-family: {$this->safeCssValue($fontFamily)};";
        }

        $dropCss = '';
        if ($settings['drop_cap']) {
            $dropCss = ' pb-drop-cap';
        }

        $columnStyle = '';
        if ($settings['column_count'] > 1) {
            $columnStyle = " column-count: {$settings['column_count']}; column-gap: {$this->safeCssValue($settings['column_gap'])};";
        }

        $hoverStyle = $this->buildHoverStyle('pb-text', $styles);

        return $hoverStyle . "<div class=\"pb-text{$dropCss}\" style=\"{$style}{$columnStyle}\">{$text}{$children}</div>";
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $text = $settings['content'];
        $alignment = $settings['alignment'];
        $color = $settings['color'] ?? '';
        $fontSize = $settings['font_size'] ?? '';
        $fontWeight = $settings['font_weight'] ?? '';
        $lineHeight = $settings['line_height'] ?? '';
        $fontFamily = $settings['font_family'] ?? '';
        $children = $content['children'] ?? '';

        $style = "text-align: {$this->safeCssValue($alignment)};";
        if ($color) {
            $style .= " color: {$this->safeCssValue($color)};";
        }
        if ($fontSize) {
            $style .= " font-size: {$this->safeCssValue($fontSize)};";
        }
        if ($fontWeight) {
            $style .= " font-weight: {$this->safeCssValue($fontWeight)};";
        }
        if ($lineHeight) {
            $style .= " line-height: {$this->safeCssValue($lineHeight)};";
        }

        if ($fontFamily) {
            $style .= " font-family: {$this->safeCssValue($fontFamily)};";
        }

        $columnStyle = '';
        if ($settings['column_count'] > 1) {
            $columnStyle = " column-count: {$settings['column_count']}; column-gap: {$this->safeCssValue($settings['column_gap'])};";
        }

        $hoverStyle = $this->buildHoverStyle('pb-text-editor', $styles);

        return $hoverStyle . "<div class=\"pb-text-editor\" style=\"{$style}{$columnStyle}\">{$text}{$children}</div>";
    }
}
