<?php

namespace App\Services\PageBuilder\Widgets;

class CounterWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'counter';
        $this->label = 'Counter';
        $this->icon = '🔢';
        $this->categories = ['general', 'interactive'];
        $this->keywords = ['counter', 'number', 'animated', 'count'];

        $this->defaultSettings = [
            'title' => 'Projetos concluídos',
            'prefix' => '',
            'suffix' => '+',
            'number' => 100,
            'duration' => 2000,
            'separator' => true,
            'alignment' => 'center',
            'color' => '#6366f1',
            'font_size' => '48px',
        ];

        $this->controls = [
            'title' => ['type' => 'text', 'label' => 'Title', 'max_length' => 200],
            'prefix' => ['type' => 'text', 'label' => 'Prefix', 'max_length' => 20],
            'suffix' => ['type' => 'text', 'label' => 'Suffix', 'max_length' => 20],
            'number' => ['type' => 'number', 'label' => 'Number', 'min' => 0, 'max' => 999999999],
            'duration' => ['type' => 'number', 'label' => 'Duration (ms)', 'min' => 500, 'max' => 10000],
            'separator' => ['type' => 'boolean', 'label' => 'Thousand Separator'],
            'alignment' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left', 'center', 'right']],
            'color' => ['type' => 'color', 'label' => 'Number Color', 'tab' => 'style'],
            'font_size' => ['type' => 'text', 'label' => 'Font Size', 'tab' => 'style'],
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
        $prefix = htmlspecialchars($settings['prefix'], ENT_QUOTES, 'UTF-8');
        $suffix = htmlspecialchars($settings['suffix'], ENT_QUOTES, 'UTF-8');
        $number = (int) $settings['number'];
        $duration = (int) $settings['duration'];
        $separator = $settings['separator'] ? '1' : '0';
        $alignment = $settings['alignment'];
        $color = htmlspecialchars($settings['color'], ENT_QUOTES, 'UTF-8');
        $fontSize = htmlspecialchars($settings['font_size'], ENT_QUOTES, 'UTF-8');

        $html = '<div class="pb-counter" style="text-align: ' . $alignment . ';">';
        $html .= '<div class="pb-counter-number" data-count="' . $number . '" data-duration="' . $duration . '" data-separator="' . $separator . '" style="font-size: ' . $fontSize . '; color: ' . $color . '; font-weight: 700;">0</div>';
        if ($title) {
            $html .= '<div class="pb-counter-title" style="font-size: 16px; color: #6b7280; margin-top: 8px;">' . $title . '</div>';
        }
        $html .= '</div>';

        $prefixData = $prefix !== '' ? ' data-prefix="' . $prefix . '"' : '';
        $suffixData = $suffix !== '' ? ' data-suffix="' . $suffix . '"' : '';

        $script = '<script>'
            . '(function(){'
            . 'var counters=document.querySelectorAll(".pb-counter-number:not([data-animated])");'
            . 'counters.forEach(function(el){'
            . 'el.dataset.animated="1";'
            . 'var target=parseInt(el.dataset.count,10);'
            . 'var duration=parseInt(el.dataset.duration,10)||2000;'
            . 'var useSep=el.dataset.separator==="1";'
            . 'var prefix=el.dataset.prefix||"";'
            . 'var suffix=el.dataset.suffix||"";'
            . 'var start=0;var startTime=null;'
            . 'function fmt(n){var s=n.toLocaleString("en-US");return prefix+s+suffix;}'
            . 'function animate(ts){'
            . 'if(!startTime)startTime=ts;'
            . 'var progress=Math.min((ts-startTime)/duration,1);'
            . 'var current=Math.floor(progress*target);'
            . 'el.textContent=useSep?fmt(current):prefix+current+suffix;'
            . 'if(progress<1)requestAnimationFrame(animate);'
            . '}'
            . 'requestAnimationFrame(animate);'
            . '});'
            . '})();'
            . '</script>';

        return $html . $script;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $title = htmlspecialchars($settings['title'], ENT_QUOTES, 'UTF-8');
        $prefix = htmlspecialchars($settings['prefix'], ENT_QUOTES, 'UTF-8');
        $suffix = htmlspecialchars($settings['suffix'], ENT_QUOTES, 'UTF-8');
        $number = (int) $settings['number'];
        $separator = $settings['separator'];
        $alignment = $settings['alignment'];
        $color = htmlspecialchars($settings['color'], ENT_QUOTES, 'UTF-8');
        $fontSize = htmlspecialchars($settings['font_size'], ENT_QUOTES, 'UTF-8');

        $displayNumber = $separator ? number_format($number) : (string) $number;

        $html = '<div style="text-align: ' . $alignment . '; padding: 12px 0;">';
        $html .= '<div style="font-size: ' . $fontSize . '; color: ' . $color . '; font-weight: 700;">' . $prefix . $displayNumber . $suffix . '</div>';
        if ($title) {
            $html .= '<div style="font-size: 14px; color: #6b7280; margin-top: 4px;">' . $title . '</div>';
        }
        $html .= '</div>';

        return $html;
    }
}
