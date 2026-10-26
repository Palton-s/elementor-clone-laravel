<?php

namespace App\Services\PageBuilder\Widgets;

class TabsWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'tabs';
        $this->label = 'Tabs';
        $this->icon = '📑';
        $this->categories = ['general'];
        $this->keywords = ['tabs', 'tab', 'navigation'];

        $this->defaultSettings = [
            'tabs' => [
                ['title' => 'Aba 1', 'content' => '<p>Conteúdo da aba 1</p>'],
                ['title' => 'Aba 2', 'content' => '<p>Conteúdo da aba 2</p>'],
            ],
            'active_tab' => 0,
            'orientation' => 'horizontal',
            'tab_color' => '#6366f1',
            'border_color' => '#e2e8f0',
            'content_padding' => 20,
        ];

        $this->controls = [
            'tabs' => ['type' => 'repeater', 'label' => 'Tabs', 'fields' => [
                'title' => ['type' => 'text', 'label' => 'Title'],
                'content' => ['type' => 'wysiwyg', 'label' => 'Content'],
            ]],
            'active_tab' => ['type' => 'number', 'label' => 'Active Tab (index)', 'min' => 0],
            'orientation' => ['type' => 'select', 'label' => 'Orientation', 'options' => ['horizontal', 'vertical']],
            'tab_color' => ['type' => 'color', 'label' => 'Active Tab Color', 'tab' => 'style'],
            'border_color' => ['type' => 'color', 'label' => 'Border Color', 'tab' => 'style'],
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
        $tabs = $settings['tabs'] ?? [];
        $activeTab = (int) $settings['active_tab'];
        $orientation = $settings['orientation'];
        $tabColor = htmlspecialchars($settings['tab_color'], ENT_QUOTES, 'UTF-8');
        $borderColor = htmlspecialchars($settings['border_color'], ENT_QUOTES, 'UTF-8');
        $padding = (int) $settings['content_padding'];

        if (empty($tabs)) {
            return '<div style="text-align:center;padding:1rem;color:#999">Nenhuma aba configurada</div>';
        }

        $isVertical = $orientation === 'vertical';
        $wrapperStyle = $isVertical ? 'display: flex; gap: 0;' : '';
        $tabsListStyle = $isVertical
            ? 'display: flex; flex-direction: column; min-width: 160px;'
            : 'display: flex; border-bottom: 2px solid ' . $borderColor . ';';

        $tabsHtml = '';
        $contentsHtml = '';

        foreach ($tabs as $i => $tab) {
            $title = htmlspecialchars($tab['title'] ?? 'Aba ' . ($i + 1), ENT_QUOTES, 'UTF-8');
            $tabHtmlContent = $tab['content'] ?? '';
            $isActive = $i === $activeTab;

            $tabBtnStyle = $isVertical
                ? 'padding: 10px 16px; cursor: pointer; font-size: 14px; border: none; text-align: left; background: ' . ($isActive ? $tabColor : 'transparent') . '; color: ' . ($isActive ? '#fff' : '#374151') . '; border-right: 3px solid ' . ($isActive ? $tabColor : $borderColor) . '; transition: all 0.2s;'
                : 'padding: 10px 20px; cursor: pointer; font-size: 14px; border: none; border-bottom: 3px solid ' . ($isActive ? $tabColor : 'transparent') . '; margin-bottom: -2px; background: ' . ($isActive ? '#fff' : 'transparent') . '; color: ' . ($isActive ? $tabColor : '#6b7280') . '; font-weight: ' . ($isActive ? '600' : '400') . '; transition: all 0.2s;';

            $tabsHtml .= '<button type="button" class="pb-tab-btn" data-tab="' . $i . '" style="' . $tabBtnStyle . '">' . $title . '</button>';

            $contentStyle = 'display: ' . ($isActive ? 'block' : 'none') . '; padding: ' . $padding . 'px;';
            $contentsHtml .= '<div class="pb-tab-content" data-tab="' . $i . '" style="' . $contentStyle . '">' . $tabHtmlContent . '</div>';
        }

        $script = '<script>'
            . '(function(){'
            . 'var wrappers=document.querySelectorAll(".pb-tabs-wrapper:not([data-bound])");'
            . 'wrappers.forEach(function(w){'
            . 'w.dataset.bound="1";'
            . 'var btns=w.querySelectorAll(".pb-tab-btn");'
            . 'var contents=w.querySelectorAll(".pb-tab-content");'
            . 'btns.forEach(function(btn){'
            . 'btn.onclick=function(){'
            . 'var idx=btn.dataset.tab;'
            . 'btns.forEach(function(b){b.style.background="transparent";b.style.color="#6b7280";b.style.fontWeight="400";b.style.borderBottom="3px solid ' . $borderColor . '";b.style.borderRight="3px solid ' . $borderColor . '";});'
            . 'contents.forEach(function(c){c.style.display="none";});'
            . 'btn.style.background="' . $tabColor . '";btn.style.color="#fff";btn.style.fontWeight="600";btn.style.borderBottom="3px solid ' . $tabColor . '";btn.style.borderRight="3px solid ' . $tabColor . '";'
            . 'contents[idx].style.display="block";'
            . '};'
            . '});'
            . '});'
            . '})();'
            . '</script>';

        return '<div class="pb-tabs-wrapper" style="' . $wrapperStyle . '">'
            . '<div class="pb-tabs-list" style="' . $tabsListStyle . '">' . $tabsHtml . '</div>'
            . '<div class="pb-tabs-content" style="flex:1;">' . $contentsHtml . '</div>'
            . '</div>'
            . $script;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $tabs = $settings['tabs'] ?? [];
        $activeTab = (int) $settings['active_tab'];
        $tabColor = htmlspecialchars($settings['tab_color'], ENT_QUOTES, 'UTF-8');
        $borderColor = htmlspecialchars($settings['border_color'], ENT_QUOTES, 'UTF-8');
        $padding = (int) $settings['content_padding'];

        if (empty($tabs)) {
            return '<div style="text-align:center;padding:1rem;color:#999">Nenhuma aba</div>';
        }

        $tabsHtml = '';
        $contentsHtml = '';

        foreach ($tabs as $i => $tab) {
            $title = htmlspecialchars($tab['title'] ?? 'Aba ' . ($i + 1), ENT_QUOTES, 'UTF-8');
            $tabContent = $tab['content'] ?? '';
            $isActive = $i === $activeTab;

            $tabsHtml .= '<button type="button" style="padding:8px 16px;cursor:default;font-size:13px;border:none;border-bottom:3px solid ' . ($isActive ? $tabColor : 'transparent') . ';margin-bottom:-2px;background:' . ($isActive ? '#fff' : 'transparent') . ';color:' . ($isActive ? $tabColor : '#6b7280') . ';font-weight:' . ($isActive ? '600' : '400') . '">' . $title . '</button>';

            $contentsHtml .= '<div style="display:' . ($isActive ? 'block' : 'none') . ';padding:' . $padding . 'px;border:1px solid #e5e7eb;border-top:none;border-radius:0 0 8px 8px;background:#fff;font-size:13px;color:#6b7280">' . ($isActive ? strip_tags(substr($tabContent, 0, 120)) . (strlen($tabContent) > 120 ? '...' : '') : '') . '</div>';
        }

        return '<div style="padding:4px">'
            . '<div style="display:flex;border-bottom:2px solid ' . $borderColor . '">' . $tabsHtml . '</div>'
            . $contentsHtml
            . '</div>';
    }
}
