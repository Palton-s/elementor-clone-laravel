<?php

namespace App\Services\PageBuilder\Widgets;

class IconBoxWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'icon_box';
        $this->label = 'Icon Box';
        $this->icon = '📦';
        $this->categories = ['general'];
        $this->keywords = ['icon', 'box', 'feature', 'description'];

        $this->defaultSettings = [
            'icon' => 'fas fa-rocket',
            'title' => 'Rápido e confiável',
            'description' => 'Nosso serviço entrega alto desempenho com 99,9% de disponibilidade garantida.',
            'icon_size' => 48,
            'icon_color' => '#6366f1',
            'title_color' => '#1f2937',
            'description_color' => '#6b7280',
            'icon_position' => 'top',
            'alignment' => 'center',
            'border_radius' => '8px',
        ];

        $this->controls = [
            'icon' => ['type' => 'icon', 'label' => 'Icon'],
            'title' => ['type' => 'text', 'label' => 'Title', 'max_length' => 200],
            'description' => ['type' => 'wysiwyg', 'label' => 'Description'],
            'icon_position' => ['type' => 'select', 'label' => 'Icon Position', 'options' => ['left', 'top']],
            'alignment' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left', 'center', 'right']],
            'icon_size' => ['type' => 'number', 'label' => 'Icon Size (px)', 'min' => 12, 'max' => 200, 'tab' => 'style'],
            'icon_color' => ['type' => 'color', 'label' => 'Icon Color', 'tab' => 'style'],
            'title_color' => ['type' => 'color', 'label' => 'Title Color', 'tab' => 'style'],
            'description_color' => ['type' => 'color', 'label' => 'Description Color', 'tab' => 'style'],
            'border_radius' => ['type' => 'text', 'label' => 'Border Radius', 'tab' => 'style'],
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
        $icon = htmlspecialchars($settings['icon'], ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars($settings['title'], ENT_QUOTES, 'UTF-8');
        $description = $settings['description'];
        $iconSize = (int) $settings['icon_size'];
        $iconColor = htmlspecialchars($settings['icon_color'], ENT_QUOTES, 'UTF-8');
        $titleColor = htmlspecialchars($settings['title_color'], ENT_QUOTES, 'UTF-8');
        $descriptionColor = htmlspecialchars($settings['description_color'], ENT_QUOTES, 'UTF-8');
        $iconPosition = $settings['icon_position'];
        $alignment = $settings['alignment'];
        $borderRadius = htmlspecialchars($settings['border_radius'], ENT_QUOTES, 'UTF-8');

        $iconHtml = '<div class="pb-icon-box-icon" style="color: ' . $iconColor . '; font-size: ' . $iconSize . 'px; line-height: 1;"><i class="' . $icon . '"></i></div>';

        $contentHtml = '<div class="pb-icon-box-content">';
        if ($title) {
            $contentHtml .= '<h3 class="pb-icon-box-title" style="color: ' . $titleColor . '; font-size: 20px; font-weight: 600; margin: 0 0 8px 0;">' . $title . '</h3>';
        }
        if ($description) {
            $contentHtml .= '<div class="pb-icon-box-description" style="color: ' . $descriptionColor . '; font-size: 15px; line-height: 1.6;">' . $description . '</div>';
        }
        $contentHtml .= '</div>';

        if ($iconPosition === 'top') {
            $html = '<div class="pb-icon-box" style="text-align: ' . $alignment . '; border-radius: ' . $borderRadius . '; padding: 24px;">';
            $html .= '<div style="margin-bottom: 16px;">' . $iconHtml . '</div>';
            $html .= $contentHtml;
            $html .= '</div>';
        } else {
            $html = '<div class="pb-icon-box" style="display: flex; align-items: flex-start; gap: 20px; text-align: ' . $alignment . '; border-radius: ' . $borderRadius . '; padding: 24px;">';
            $html .= '<div style="flex-shrink: 0;">' . $iconHtml . '</div>';
            $html .= $contentHtml;
            $html .= '</div>';
        }

        return $html;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $icon = htmlspecialchars($settings['icon'], ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars($settings['title'], ENT_QUOTES, 'UTF-8');
        $description = strip_tags($settings['description'] ?? '');
        $iconSize = (int) $settings['icon_size'];
        $iconColor = htmlspecialchars($settings['icon_color'], ENT_QUOTES, 'UTF-8');
        $titleColor = htmlspecialchars($settings['title_color'], ENT_QUOTES, 'UTF-8');
        $descriptionColor = htmlspecialchars($settings['description_color'], ENT_QUOTES, 'UTF-8');
        $iconPosition = $settings['icon_position'];
        $alignment = $settings['alignment'];
        $borderRadius = htmlspecialchars($settings['border_radius'], ENT_QUOTES, 'UTF-8');

        $iconHtml = '<div style="color: ' . $iconColor . '; font-size: ' . $iconSize . 'px; line-height: 1;"><i class="' . $icon . '"></i></div>';

        $contentHtml = '<div>';
        if ($title) {
            $contentHtml .= '<div style="color: ' . $titleColor . '; font-size: 16px; font-weight: 600; margin-bottom: 4px;">' . $title . '</div>';
        }
        if ($description) {
            $contentHtml .= '<div style="color: ' . $descriptionColor . '; font-size: 13px; line-height: 1.5;">' . htmlspecialchars(mb_substr($description, 0, 120), ENT_QUOTES, 'UTF-8') . (mb_strlen($description) > 120 ? '...' : '') . '</div>';
        }
        $contentHtml .= '</div>';

        if ($iconPosition === 'top') {
            $html = '<div style="text-align: ' . $alignment . '; padding: 12px;">';
            $html .= '<div style="margin-bottom: 8px;">' . $iconHtml . '</div>';
            $html .= $contentHtml;
            $html .= '</div>';
        } else {
            $html = '<div style="display: flex; align-items: flex-start; gap: 12px; padding: 12px;">';
            $html .= '<div style="flex-shrink: 0;">' . $iconHtml . '</div>';
            $html .= $contentHtml;
            $html .= '</div>';
        }

        return $html;
    }
}
