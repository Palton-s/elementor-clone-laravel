<?php

namespace App\Services\PageBuilder\Widgets;

class ProgressBarWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'progress_bar';
        $this->label = 'Progress Bar';
        $this->icon = '📊';
        $this->categories = ['general', 'interactive'];
        $this->keywords = ['progress', 'bar', 'percentage', 'skill'];

        $this->defaultSettings = [
            'title' => 'Desenvolvimento web',
            'percentage' => 70,
            'height' => '20px',
            'color' => '#6366f1',
            'background_color' => '#e2e8f0',
            'show_percentage' => true,
            'animate' => true,
            'border_radius' => '10px',
            'alignment' => 'left',
        ];

        $this->controls = [
            'title' => ['type' => 'text', 'label' => 'Title', 'max_length' => 200],
            'percentage' => ['type' => 'number', 'label' => 'Percentage', 'min' => 0, 'max' => 100],
            'height' => ['type' => 'text', 'label' => 'Bar Height'],
            'color' => ['type' => 'color', 'label' => 'Bar Color', 'tab' => 'style'],
            'background_color' => ['type' => 'color', 'label' => 'Background Color', 'tab' => 'style'],
            'show_percentage' => ['type' => 'boolean', 'label' => 'Show Percentage Text'],
            'animate' => ['type' => 'boolean', 'label' => 'Animate'],
            'border_radius' => ['type' => 'text', 'label' => 'Border Radius', 'tab' => 'style'],
            'alignment' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left', 'center', 'right']],
            'background' => ['type' => 'background', 'label' => 'Background', 'tab' => 'style'],
            'border' => ['type' => 'border', 'label' => 'Border', 'tab' => 'style'],
            'box_shadow' => ['type' => 'box_shadow', 'label' => 'Box Shadow', 'tab' => 'style'],
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
        $title = htmlspecialchars($settings['title'], ENT_QUOTES, 'UTF-8');
        $percentage = min(100, max(0, (int) $settings['percentage']));
        $height = htmlspecialchars($settings['height'], ENT_QUOTES, 'UTF-8');
        $color = htmlspecialchars($settings['color'], ENT_QUOTES, 'UTF-8');
        $bgColor = htmlspecialchars($settings['background_color'], ENT_QUOTES, 'UTF-8');
        $showPct = $settings['show_percentage'];
        $animate = $settings['animate'];
        $borderRadius = htmlspecialchars($settings['border_radius'], ENT_QUOTES, 'UTF-8');
        $alignment = $settings['alignment'];

        $barStyle = 'width: ' . ($animate ? '0' : $percentage) . '%; height: ' . $height . '; background-color: ' . $color . '; border-radius: ' . $borderRadius . '; transition: width 1s ease-in-out;';
        if ($animate) {
            $barStyle .= ' width: 0; height: ' . $height . '; background-color: ' . $color . '; border-radius: ' . $borderRadius . '; transition: width 1s ease-in-out;';
        }

        $html = '<div class="pb-progress-bar" style="width: 100%; text-align: ' . $alignment . ';">';
        if ($title) {
            $html .= '<div class="pb-progress-title" style="font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 8px;">' . $title . '</div>';
        }
        $html .= '<div style="background-color: ' . $bgColor . '; border-radius: ' . $borderRadius . '; overflow: hidden; width: 100%;">';
        $html .= '<div class="pb-progress-inner" data-target-width="' . $percentage . '" style="' . $barStyle . '"></div>';
        $html .= '</div>';
        if ($showPct) {
            $html .= '<div class="pb-progress-pct" style="font-size: 14px; font-weight: 600; color: ' . $color . '; margin-top: 4px;">' . $percentage . '%</div>';
        }
        $html .= '</div>';

        $script = '<script>'
            . '(function(){'
            . 'var bars=document.querySelectorAll(".pb-progress-inner:not([data-animated])");'
            . 'bars.forEach(function(bar){'
            . 'bar.dataset.animated="1";'
            . 'var target=bar.dataset.targetWidth;'
            . 'setTimeout(function(){bar.style.width=target+"%";},200);'
            . '});'
            . '})();'
            . '</script>';

        return $html . $script;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $title = htmlspecialchars($settings['title'], ENT_QUOTES, 'UTF-8');
        $percentage = min(100, max(0, (int) $settings['percentage']));
        $height = htmlspecialchars($settings['height'], ENT_QUOTES, 'UTF-8');
        $color = htmlspecialchars($settings['color'], ENT_QUOTES, 'UTF-8');
        $bgColor = htmlspecialchars($settings['background_color'], ENT_QUOTES, 'UTF-8');
        $showPct = $settings['show_percentage'];
        $borderRadius = htmlspecialchars($settings['border_radius'], ENT_QUOTES, 'UTF-8');

        $html = '<div style="padding: 8px 0;">';
        if ($title) {
            $html .= '<div style="font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">' . $title . '</div>';
        }
        $html .= '<div style="background-color: ' . $bgColor . '; border-radius: ' . $borderRadius . '; overflow: hidden; width: 100%;">';
        $html .= '<div style="width: ' . $percentage . '%; height: ' . $height . '; background-color: ' . $color . '; border-radius: ' . $borderRadius . ';"></div>';
        $html .= '</div>';
        if ($showPct) {
            $html .= '<div style="font-size: 12px; font-weight: 600; color: ' . $color . '; margin-top: 4px;">' . $percentage . '%</div>';
        }
        $html .= '</div>';

        return $html;
    }
}
