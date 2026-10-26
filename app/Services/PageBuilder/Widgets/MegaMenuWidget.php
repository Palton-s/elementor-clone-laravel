<?php

namespace App\Services\PageBuilder\Widgets;

class MegaMenuWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'mega_menu';
        $this->label = 'Mega Menu';
        $this->icon = 'fas fa-bars';
        $this->categories = ['pro'];
        $this->container = true;
        $this->keywords = ['menu', 'navigation', 'mega', 'nav'];

        $this->defaultSettings = [
            'layout' => 'horizontal',
            'alignment' => 'left',
            'background_color' => '#ffffff',
            'item_padding' => ['top' => '12px', 'right' => '20px', 'bottom' => '12px', 'left' => '20px'],
            'item_spacing' => 0,
            'typography' => ['font_family' => '', 'font_size' => '14px', 'font_weight' => '500'],
            'hover_background' => '#f3f4f6',
            'hover_color' => '#6366f1',
            'submenu_width' => 200,
            'submenu_background' => '#ffffff',
            'breakpoint' => 'tablet',
            'menu_items' => [],
        ];

        $this->controls = [
            'menu_items' => [
                'type' => 'repeater',
                'label' => 'Menu Items',
                'fields' => [
                    'label' => ['type' => 'text', 'label' => 'Label', 'required' => true],
                    'link' => ['type' => 'url', 'label' => 'Link'],
                    'icon' => ['type' => 'select', 'label' => 'Icon', 'options' => [
                        'fas fa-home', 'fas fa-info-circle', 'fas fa-cogs', 'fas fa-envelope',
                        'fas fa-user', 'fas fa-shopping-cart', 'fas fa-search', 'fas fa-bell',
                        'fas fa-star', 'fas fa-heart', 'fas fa-check', 'fas fa-times',
                        'fas fa-arrow-right', 'fas fa-arrow-left', 'fas fa-chevron-down',
                        'fas fa-bars', 'fas fa-th', 'fas fa-file', 'fas fa-image',
                        'fas fa-video', 'fas fa-music', 'fas fa-phone', 'fas fa-map-marker-alt',
                        'fas fa-globe', 'fas fa-lock', 'fas fa-download', 'fas fa-upload',
                    ]],
                    'target' => ['type' => 'select', 'label' => 'Target', 'options' => ['_self', '_blank']],
                    'css_classes' => ['type' => 'text', 'label' => 'CSS Classes'],
                ],
            ],
            'layout' => ['type' => 'select', 'label' => 'Layout', 'options' => ['horizontal', 'vertical']],
            'alignment' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left', 'center', 'right']],
            'background_color' => ['type' => 'color', 'label' => 'Background Color', 'tab' => 'style'],
            'item_padding' => ['type' => 'dimensions', 'label' => 'Item Padding', 'tab' => 'style'],
            'item_spacing' => ['type' => 'number', 'label' => 'Item Spacing (px)', 'min' => 0, 'max' => 100, 'tab' => 'style'],
            'typography' => ['type' => 'typography', 'label' => 'Typography', 'tab' => 'style'],
            'hover_background' => ['type' => 'color', 'label' => 'Hover Background', 'tab' => 'style'],
            'hover_color' => ['type' => 'color', 'label' => 'Hover Color', 'tab' => 'style'],
            'submenu_width' => ['type' => 'number', 'label' => 'Submenu Width (px)', 'default' => 200, 'min' => 100, 'max' => 1200, 'tab' => 'advanced'],
            'submenu_background' => ['type' => 'color', 'label' => 'Submenu Background', 'tab' => 'advanced'],
            'breakpoint' => ['type' => 'select', 'label' => 'Breakpoint', 'options' => ['mobile', 'tablet', 'desktop'], 'tab' => 'advanced'],
        ];
    }

    public function render(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $menuItems = $settings['menu_items'] ?? [];
        $layout = htmlspecialchars($settings['layout'], ENT_QUOTES, 'UTF-8');
        $alignment = htmlspecialchars($settings['alignment'], ENT_QUOTES, 'UTF-8');
        $bgColor = htmlspecialchars($settings['background_color'], ENT_QUOTES, 'UTF-8');
        $itemPadding = $settings['item_padding'];
        $itemSpacing = (int) $settings['item_spacing'];
        $typography = $settings['typography'];
        $hoverBg = htmlspecialchars($settings['hover_background'], ENT_QUOTES, 'UTF-8');
        $hoverColor = htmlspecialchars($settings['hover_color'], ENT_QUOTES, 'UTF-8');
        $submenuWidth = (int) $settings['submenu_width'];
        $submenuBg = htmlspecialchars($settings['submenu_background'], ENT_QUOTES, 'UTF-8');

        $alignMap = ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'];
        $alignVal = $alignMap[$alignment] ?? 'flex-start';
        $direction = $layout === 'vertical' ? 'column' : 'row';

        $pt = $itemPadding['top'] ?? '12px';
        $pr = $itemPadding['right'] ?? '20px';
        $pb = $itemPadding['bottom'] ?? '12px';
        $pl = $itemPadding['left'] ?? '20px';

        $fontFamily = $typography['font_family'] ?? '';
        $fontSize = $typography['font_size'] ?? '14px';
        $fontWeight = $typography['font_weight'] ?? '500';

        $navStyle = "background-color: {$bgColor}; display: flex; flex-direction: {$direction}; align-items: {$alignVal};";
        if ($itemSpacing > 0) {
            $navStyle .= $direction === 'row' ? " gap: {$itemSpacing}px;" : " gap: {$itemSpacing}px;";
        }

        $itemsHtml = '';
        foreach ($menuItems as $item) {
            $label = htmlspecialchars($item['label'] ?? '', ENT_QUOTES, 'UTF-8');
            $link = htmlspecialchars($item['link'] ?? '#', ENT_QUOTES, 'UTF-8');
            $icon = htmlspecialchars($item['icon'] ?? '', ENT_QUOTES, 'UTF-8');
            $target = htmlspecialchars($item['target'] ?? '_self', ENT_QUOTES, 'UTF-8');
            $cssClasses = htmlspecialchars($item['css_classes'] ?? '', ENT_QUOTES, 'UTF-8');

            $itemStyle = "padding: {$pt} {$pr} {$pb} {$pl}; font-family: {$fontFamily}; font-size: {$fontSize}; font-weight: {$fontWeight}; display: flex; align-items: center; gap: 8px; text-decoration: none; color: inherit; border-radius: 4px; transition: all 300ms ease;";
            $iconHtml = $icon ? "<i class=\"{$icon}\"></i>" : '';

            $itemsHtml .= "<li style=\"list-style: none; margin: 0;\"><a href=\"{$link}\" target=\"{$target}\" class=\"pb-mega-menu-item {$cssClasses}\" style=\"{$itemStyle}\">{$iconHtml}{$label}</a></li>";
        }

        $hoverStyle = "<style>.pb-mega-menu-item:hover { background-color: {$hoverBg} !important; color: {$hoverColor} !important; }</style>";

        if ($submenuWidth !== 200) {
            $hoverStyle .= "<style>.pb-mega-menu .pb-submenu { width: {$submenuWidth}px; }</style>";
        }
        if ($submenuBg !== '#ffffff') {
            $hoverStyle .= "<style>.pb-mega-menu .pb-submenu { background-color: {$submenuBg}; }</style>";
        }

        $listHtml = empty($itemsHtml) ? '' : "<ul style=\"display: flex; flex-direction: {$direction}; align-items: {$alignVal}; list-style: none; margin: 0; padding: 0; gap: {$itemSpacing}px;\">{$itemsHtml}</ul>";
        // It is a container: elements dropped into it (logo, button, search...) sit next to the links
        $children = $content['children'] ?? '';

        return $hoverStyle . "<nav class=\"pb-mega-menu\" style=\"{$navStyle}\">{$listHtml}{$children}</nav>";
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $menuItems = $settings['menu_items'] ?? [];
        $layout = htmlspecialchars($settings['layout'], ENT_QUOTES, 'UTF-8');
        $alignment = htmlspecialchars($settings['alignment'], ENT_QUOTES, 'UTF-8');
        $bgColor = htmlspecialchars($settings['background_color'], ENT_QUOTES, 'UTF-8');
        $itemPadding = $settings['item_padding'];
        $itemSpacing = (int) $settings['item_spacing'];
        $typography = $settings['typography'];
        $hoverBg = htmlspecialchars($settings['hover_background'], ENT_QUOTES, 'UTF-8');
        $hoverColor = htmlspecialchars($settings['hover_color'], ENT_QUOTES, 'UTF-8');
        $submenuWidth = (int) $settings['submenu_width'];
        $submenuBg = htmlspecialchars($settings['submenu_background'], ENT_QUOTES, 'UTF-8');

        $alignMap = ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'];
        $alignVal = $alignMap[$alignment] ?? 'flex-start';
        $direction = $layout === 'vertical' ? 'column' : 'row';

        $pt = $itemPadding['top'] ?? '12px';
        $pr = $itemPadding['right'] ?? '20px';
        $pb = $itemPadding['bottom'] ?? '12px';
        $pl = $itemPadding['left'] ?? '20px';

        $fontFamily = $typography['font_family'] ?? '';
        $fontSize = $typography['font_size'] ?? '14px';
        $fontWeight = $typography['font_weight'] ?? '500';

        $navStyle = "background-color: {$bgColor}; display: flex; flex-direction: {$direction}; align-items: {$alignVal}; min-height: 40px;";
        if ($itemSpacing > 0) {
            $navStyle .= $direction === 'row' ? " gap: {$itemSpacing}px;" : " gap: {$itemSpacing}px;";
        }

        $itemsHtml = '';
        foreach ($menuItems as $item) {
            $label = htmlspecialchars($item['label'] ?? '', ENT_QUOTES, 'UTF-8');
            $link = htmlspecialchars($item['link'] ?? '#', ENT_QUOTES, 'UTF-8');
            $icon = htmlspecialchars($item['icon'] ?? '', ENT_QUOTES, 'UTF-8');
            $target = htmlspecialchars($item['target'] ?? '_self', ENT_QUOTES, 'UTF-8');
            $cssClasses = htmlspecialchars($item['css_classes'] ?? '', ENT_QUOTES, 'UTF-8');

            $itemStyle = "padding: {$pt} {$pr} {$pb} {$pl}; font-family: {$fontFamily}; font-size: {$fontSize}; font-weight: {$fontWeight}; display: flex; align-items: center; gap: 8px; text-decoration: none; color: inherit; border-radius: 4px; transition: all 300ms ease;";
            $iconHtml = $icon ? "<i class=\"{$icon}\"></i>" : '';

            $itemsHtml .= "<li style=\"list-style: none; margin: 0;\"><a href=\"{$link}\" target=\"{$target}\" class=\"pb-mega-menu-item-editor {$cssClasses}\" style=\"{$itemStyle}\">{$iconHtml}{$label}</a></li>";
        }

        $hoverStyle = "<style>.pb-mega-menu-item-editor:hover { background-color: {$hoverBg} !important; color: {$hoverColor} !important; }</style>";

        $listHtml = empty($itemsHtml) ? '' : "<ul style=\"display: flex; flex-direction: {$direction}; align-items: {$alignVal}; list-style: none; margin: 0; padding: 0; gap: {$itemSpacing}px;\">{$itemsHtml}</ul>";

        return $hoverStyle . "<nav class=\"pb-mega-menu-editor\" style=\"{$navStyle}\">{$listHtml}</nav>";
    }
}
