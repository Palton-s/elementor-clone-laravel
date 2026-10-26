<?php

namespace App\Services\PageBuilder\Widgets;

class SocialIconsWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'social_icons';
        $this->label = 'Social Icons';
        $this->icon = '📱';
        $this->categories = ['general', 'social'];
        $this->keywords = ['social', 'icons', 'links', 'media'];

        $this->defaultSettings = [
            'icons' => [
                ['platform' => 'facebook', 'url' => '#', 'color' => '#1877f2'],
                ['platform' => 'twitter', 'url' => '#', 'color' => '#1da1f2'],
                ['platform' => 'instagram', 'url' => '#', 'color' => '#e4405f'],
            ],
            'columns' => '3',
            'icon_size' => 32,
            'gap' => 10,
            'alignment' => 'center',
        ];

        $this->controls = [
            'icons' => ['type' => 'repeater', 'label' => 'Social Icons', 'fields' => [
                'platform' => ['type' => 'select', 'label' => 'Platform', 'options' => ['facebook', 'twitter', 'instagram', 'youtube', 'linkedin', 'github', 'email', 'phone']],
                'url' => ['type' => 'url', 'label' => 'URL'],
                'color' => ['type' => 'color', 'label' => 'Color'],
            ]],
            'columns' => ['type' => 'select', 'label' => 'Columns', 'options' => ['1', '2', '3', '4', '5', '6']],
            'icon_size' => ['type' => 'number', 'label' => 'Icon Size (px)', 'min' => 16, 'max' => 100],
            'gap' => ['type' => 'number', 'label' => 'Gap (px)', 'min' => 0, 'max' => 50],
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

    protected function getPlatformIcon(string $platform): string
    {
        return match ($platform) {
            'facebook' => 'fab fa-facebook-f',
            'twitter' => 'fab fa-twitter',
            'instagram' => 'fab fa-instagram',
            'youtube' => 'fab fa-youtube',
            'linkedin' => 'fab fa-linkedin-in',
            'github' => 'fab fa-github',
            'email' => 'fas fa-envelope',
            'phone' => 'fas fa-phone',
            default => 'fas fa-globe',
        };
    }

    public function render(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $icons = $settings['icons'] ?? [];
        $columns = htmlspecialchars($settings['columns'], ENT_QUOTES, 'UTF-8');
        $iconSize = (int) $settings['icon_size'];
        $gap = (int) $settings['gap'];
        $alignment = $settings['alignment'];

        if (empty($icons)) {
            return '<div style="text-align:center;padding:1rem;color:#999">Nenhum ícone configurado</div>';
        }

        $html = '<div class="pb-social-icons" style="display: grid; grid-template-columns: repeat(' . $columns . ', 1fr); gap: ' . $gap . 'px; text-align: ' . $alignment . ';">';
        foreach ($icons as $icon) {
            $platform = htmlspecialchars($icon['platform'] ?? 'globe', ENT_QUOTES, 'UTF-8');
            $url = htmlspecialchars($icon['url'] ?? '#', ENT_QUOTES, 'UTF-8');
            $color = htmlspecialchars($icon['color'] ?? '#6b7280', ENT_QUOTES, 'UTF-8');
            $iconClass = $this->getPlatformIcon($platform);
            $halfSize = $iconSize / 2;

            $html .= '<a href="' . $url . '" class="pb-social-icon-link" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; justify-content: center; width: ' . $iconSize . 'px; height: ' . $iconSize . 'px; background: ' . $color . '; color: #fff; border-radius: 50%; text-decoration: none; font-size: ' . ($iconSize * 0.45) . 'px; transition: transform 0.3s ease, opacity 0.3s ease;">';
            $html .= '<i class="' . $iconClass . '"></i>';
            $html .= '</a>';
        }
        $html .= '</div>';

        return $html;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $icons = $settings['icons'] ?? [];
        $columns = htmlspecialchars($settings['columns'], ENT_QUOTES, 'UTF-8');
        $iconSize = (int) $settings['icon_size'];
        $gap = (int) $settings['gap'];
        $alignment = $settings['alignment'];

        if (empty($icons)) {
            return '<div style="text-align:center;padding:1rem;color:#999">Nenhum ícone</div>';
        }

        $html = '<div style="display: grid; grid-template-columns: repeat(' . $columns . ', 1fr); gap: ' . $gap . 'px; text-align: ' . $alignment . '; padding: 8px 0;">';
        foreach ($icons as $icon) {
            $platform = htmlspecialchars($icon['platform'] ?? 'globe', ENT_QUOTES, 'UTF-8');
            $color = htmlspecialchars($icon['color'] ?? '#6b7280', ENT_QUOTES, 'UTF-8');
            $iconClass = $this->getPlatformIcon($platform);

            $html .= '<div style="display: inline-flex; align-items: center; justify-content: center; width: ' . $iconSize . 'px; height: ' . $iconSize . 'px; background: ' . $color . '; color: #fff; border-radius: 50%; font-size: ' . ($iconSize * 0.45) . 'px;">';
            $html .= '<i class="' . $iconClass . '"></i>';
            $html .= '</div>';
        }
        $html .= '</div>';

        return $html;
    }
}
