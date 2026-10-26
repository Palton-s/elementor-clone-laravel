<?php

namespace App\Services\PageBuilder\Widgets;

class AccordionWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'accordion';
        $this->label = 'Accordion';
        $this->icon = '🪗';
        $this->categories = ['general'];
        $this->keywords = ['accordion', 'toggle', 'collapse', 'faq'];

        $this->defaultSettings = [
            'items' => [
                ['title' => 'Item 1', 'content' => '<p>Conteúdo do item 1</p>', 'open' => true],
                ['title' => 'Item 2', 'content' => '<p>Conteúdo do item 2</p>', 'open' => false],
                ['title' => 'Item 3', 'content' => '<p>Conteúdo do item 3</p>', 'open' => false],
            ],
            'active_item' => 0,
            'icon_position' => 'right',
            'tab_color' => '#6366f1',
            'border_color' => '#e2e8f0',
            'item_spacing' => 0,
            'content_padding' => 16,
        ];

        $this->controls = [
            'items' => ['type' => 'repeater', 'label' => 'Items', 'fields' => [
                'title' => ['type' => 'text', 'label' => 'Title'],
                'content' => ['type' => 'wysiwyg', 'label' => 'Content'],
                'open' => ['type' => 'boolean', 'label' => 'Open by Default'],
            ]],
            'icon_position' => ['type' => 'select', 'label' => 'Icon Position', 'options' => ['left', 'right']],
            'tab_color' => ['type' => 'color', 'label' => 'Active Color', 'tab' => 'style'],
            'border_color' => ['type' => 'color', 'label' => 'Border Color', 'tab' => 'style'],
            'item_spacing' => ['type' => 'number', 'label' => 'Item Spacing (px)', 'min' => 0, 'max' => 20, 'tab' => 'style'],
            'content_padding' => ['type' => 'number', 'label' => 'Content Padding (px)', 'min' => 0, 'max' => 60, 'tab' => 'style'],
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
        $items = $settings['items'] ?? [];
        $iconPos = $settings['icon_position'];
        $tabColor = htmlspecialchars($settings['tab_color'], ENT_QUOTES, 'UTF-8');
        $borderColor = htmlspecialchars($settings['border_color'], ENT_QUOTES, 'UTF-8');
        $spacing = (int) $settings['item_spacing'];
        $padding = (int) $settings['content_padding'];

        if (empty($items)) {
            return '<div style="text-align:center;padding:1rem;color:#999">Nenhum item configurado</div>';
        }

        $itemsHtml = '';
        foreach ($items as $i => $item) {
            $title = htmlspecialchars($item['title'] ?? 'Item ' . ($i + 1), ENT_QUOTES, 'UTF-8');
            $itemContent = $item['content'] ?? '';
            $isOpen = !empty($item['open']);

            $chevron = $iconPos === 'left'
                ? '<span style="transition:transform 0.2s;display:inline-block;transform:rotate(' . ($isOpen ? '90deg' : '0deg') . ');margin-right:8px">▶</span>'
                : '<span style="transition:transform 0.2s;display:inline-block;transform:rotate(' . ($isOpen ? '90deg' : '0deg') . ');margin-left:auto">▶</span>';

            $headerStyle = 'display:flex;align-items:center;padding:12px 16px;cursor:pointer;font-size:15px;font-weight:500;border:1px solid ' . $borderColor . ';background:' . ($isOpen ? $tabColor : '#fff') . ';color:' . ($isOpen ? '#fff' : '#374151') . ';transition:all 0.2s;';

            $contentStyle = 'display:' . ($isOpen ? 'block' : 'none') . ';padding:' . $padding . 'px;border:1px solid ' . $borderColor . ';border-top:none;background:#fff;font-size:14px;line-height:1.6;color:#4b5563;';

            $itemsHtml .= '<div class="pb-accordion-item" data-index="' . $i . '" style="margin-bottom:' . $spacing . 'px;border-radius:8px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06)">';

            if ($iconPos === 'left') {
                $itemsHtml .= '<div class="pb-accordion-header" style="' . $headerStyle . '">' . $chevron . $title . '</div>';
            } else {
                $itemsHtml .= '<div class="pb-accordion-header" style="' . $headerStyle . '">' . $title . $chevron . '</div>';
            }

            $itemsHtml .= '<div class="pb-accordion-content" style="' . $contentStyle . '">' . $itemContent . '</div>';
            $itemsHtml .= '</div>';
        }

        $script = '<script>'
            . '(function(){'
            . 'var accordions=document.querySelectorAll(".pb-accordion:not([data-bound])");'
            . 'accordions.forEach(function(acc){'
            . 'acc.dataset.bound="1";'
            . 'var headers=acc.querySelectorAll(".pb-accordion-header");'
            . 'headers.forEach(function(h){'
            . 'h.onclick=function(){'
            . 'var item=h.closest(".pb-accordion-item");'
            . 'var content=item.querySelector(".pb-accordion-content");'
            . 'var chevron=h.querySelector("span");'
            . 'var isOpen=content.style.display==="block";'
            . 'content.style.display=isOpen?"none":"block";'
            . 'if(chevron)chevron.style.transform=isOpen?"rotate(0deg)":"rotate(90deg)";'
            . 'h.style.background=isOpen?"#fff":"' . $tabColor . '";'
            . 'h.style.color=isOpen?"#374151":"#fff";'
            . '};'
            . '});'
            . '});'
            . '})();'
            . '</script>';

        return '<div class="pb-accordion" style="width:100%">' . $itemsHtml . '</div>' . $script;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $items = $settings['items'] ?? [];
        $tabColor = htmlspecialchars($settings['tab_color'], ENT_QUOTES, 'UTF-8');
        $borderColor = htmlspecialchars($settings['border_color'], ENT_QUOTES, 'UTF-8');
        $spacing = (int) $settings['item_spacing'];
        $padding = (int) $settings['content_padding'];

        if (empty($items)) {
            return '<div style="text-align:center;padding:1rem;color:#999">Nenhum item</div>';
        }

        $itemsHtml = '';
        foreach ($items as $i => $item) {
            $title = htmlspecialchars($item['title'] ?? 'Item ' . ($i + 1), ENT_QUOTES, 'UTF-8');
            $tabContent = $item['content'] ?? '';
            $isOpen = !empty($item['open']);

            $itemsHtml .= '<div style="margin-bottom:' . $spacing . 'px;border-radius:8px;overflow:hidden;border:1px solid ' . $borderColor . '">';
            $itemsHtml .= '<div style="display:flex;align-items:center;padding:10px 14px;font-size:13px;font-weight:500;background:' . ($isOpen ? $tabColor : '#f9fafb') . ';color:' . ($isOpen ? '#fff' : '#374151') . '">';
            $itemsHtml .= '<span style="display:inline-block;transform:rotate(' . ($isOpen ? '90deg' : '0deg') . ');margin-right:8px;font-size:10px">▶</span>';
            $itemsHtml .= $title;
            $itemsHtml .= '</div>';
            if ($isOpen) {
                $itemsHtml .= '<div style="padding:' . $padding . 'px;font-size:12px;color:#6b7280;background:#fff">' . strip_tags(substr($tabContent, 0, 100)) . (strlen($tabContent) > 100 ? '...' : '') . '</div>';
            }
            $itemsHtml .= '</div>';
        }

        return '<div style="padding:4px">' . $itemsHtml . '</div>';
    }
}
