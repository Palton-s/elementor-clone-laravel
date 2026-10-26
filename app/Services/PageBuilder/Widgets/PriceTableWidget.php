<?php

namespace App\Services\PageBuilder\Widgets;

class PriceTableWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'price_table';
        $this->label = 'Price Table';
        $this->icon = '💰';
        $this->categories = ['general', 'ecommerce'];
        $this->keywords = ['price', 'pricing', 'table', 'plan', 'package'];

        $this->defaultSettings = [
            'title' => 'Profissional',
            'price' => '29',
            'currency' => 'R$',
            'period' => '/mês',
            'features' => [
                ['text' => '5 projetos', 'included' => true],
                ['text' => '10 GB de armazenamento', 'included' => true],
                ['text' => 'Suporte prioritário', 'included' => true],
                ['text' => 'Domínio próprio', 'included' => false],
                ['text' => 'Acesso à API', 'included' => false],
            ],
            'button_text' => 'Escolher',
            'button_link' => '#',
            'featured' => false,
            'featured_color' => '#6366f1',
        ];

        $this->controls = [
            'title' => ['type' => 'text', 'label' => 'Title', 'required' => true, 'max_length' => 100],
            'price' => ['type' => 'text', 'label' => 'Price', 'max_length' => 20],
            'currency' => ['type' => 'text', 'label' => 'Currency', 'max_length' => 10],
            'period' => ['type' => 'text', 'label' => 'Period', 'max_length' => 30],
            'features' => ['type' => 'repeater', 'label' => 'Features', 'fields' => [
                'text' => ['type' => 'text', 'label' => 'Feature Text'],
                'included' => ['type' => 'boolean', 'label' => 'Included'],
            ]],
            'button_text' => ['type' => 'text', 'label' => 'Button Text', 'max_length' => 50],
            'button_link' => ['type' => 'url', 'label' => 'Button Link'],
            'featured' => ['type' => 'boolean', 'label' => 'Featured Plan'],
            'featured_color' => ['type' => 'color', 'label' => 'Featured Color', 'tab' => 'style'],
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
        $price = htmlspecialchars($settings['price'], ENT_QUOTES, 'UTF-8');
        $currency = htmlspecialchars($settings['currency'], ENT_QUOTES, 'UTF-8');
        $period = htmlspecialchars($settings['period'], ENT_QUOTES, 'UTF-8');
        $features = $settings['features'] ?? [];
        $buttonText = htmlspecialchars($settings['button_text'], ENT_QUOTES, 'UTF-8');
        $buttonLink = htmlspecialchars($settings['button_link'], ENT_QUOTES, 'UTF-8');
        $featured = $settings['featured'];
        $featuredColor = htmlspecialchars($settings['featured_color'], ENT_QUOTES, 'UTF-8');

        $cardBorder = $featured ? '2px solid ' . $featuredColor : '1px solid #e5e7eb';
        $headerBg = $featured ? $featuredColor : '#f9fafb';
        $headerColor = $featured ? '#fff' : '#1f2937';

        $html = '<div class="pb-price-table" style="max-width: 360px; border-radius: 12px; overflow: hidden; border: ' . $cardBorder . '; box-shadow: 0 4px 6px rgba(0,0,0,0.07); text-align: center;">';

        // Header
        $html .= '<div style="background: ' . $headerBg . '; padding: 32px 24px;">';
        $html .= '<div style="color: ' . $headerColor . '; font-size: 22px; font-weight: 600; margin-bottom: 16px;">' . $title . '</div>';
        $html .= '<div style="color: ' . $headerColor . '; font-size: 14px; opacity: 0.8;">';
        $html .= '<span style="font-size: 20px; vertical-align: top; line-height: 1;">' . $currency . '</span>';
        $html .= '<span style="font-size: 48px; font-weight: 700; line-height: 1;">' . $price . '</span>';
        if ($period) {
            $html .= '<span style="font-size: 14px; opacity: 0.7;">' . $period . '</span>';
        }
        $html .= '</div>';
        $html .= '</div>';

        // Features
        $html .= '<div style="padding: 24px;">';
        $html .= '<ul style="list-style: none; padding: 0; margin: 0; text-align: left;">';
        foreach ($features as $feature) {
            $featureText = htmlspecialchars($feature['text'] ?? '', ENT_QUOTES, 'UTF-8');
            $included = !empty($feature['included']);
            $checkColor = $included ? '#10b981' : '#d1d5db';
            $checkIcon = $included ? '✓' : '✕';
            $textStyle = $included ? 'color: #374151;' : 'color: #9ca3af; text-decoration: line-through;';
            $html .= '<li style="padding: 10px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; display: flex; align-items: center; gap: 10px;">';
            $html .= '<span style="color: ' . $checkColor . '; font-weight: 700; flex-shrink: 0;">' . $checkIcon . '</span>';
            $html .= '<span style="' . $textStyle . '">' . $featureText . '</span>';
            $html .= '</li>';
        }
        $html .= '</ul>';

        // Button
        if ($buttonText) {
            $btnBg = $featured ? $featuredColor : '#fff';
            $btnColor = $featured ? '#fff' : $featuredColor;
            $btnBorder = $featured ? 'none' : '2px solid ' . $featuredColor;
            $html .= '<a href="' . $buttonLink . '" class="pb-price-table-btn" style="display: block; margin-top: 24px; padding: 14px 24px; background: ' . $btnBg . '; color: ' . $btnColor . '; border: ' . $btnBorder . '; border-radius: 8px; font-weight: 600; font-size: 16px; text-decoration: none; text-align: center; transition: all 0.3s ease;">' . $buttonText . '</a>';
        }
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $title = htmlspecialchars($settings['title'], ENT_QUOTES, 'UTF-8');
        $price = htmlspecialchars($settings['price'], ENT_QUOTES, 'UTF-8');
        $currency = htmlspecialchars($settings['currency'], ENT_QUOTES, 'UTF-8');
        $period = htmlspecialchars($settings['period'], ENT_QUOTES, 'UTF-8');
        $features = $settings['features'] ?? [];
        $buttonText = htmlspecialchars($settings['button_text'], ENT_QUOTES, 'UTF-8');
        $featured = $settings['featured'];
        $featuredColor = htmlspecialchars($settings['featured_color'], ENT_QUOTES, 'UTF-8');

        $cardBorder = $featured ? '2px solid ' . $featuredColor : '1px solid #e5e7eb';
        $headerBg = $featured ? $featuredColor : '#f9fafb';
        $headerColor = $featured ? '#fff' : '#1f2937';

        $html = '<div style="max-width: 300px; border-radius: 8px; overflow: hidden; border: ' . $cardBorder . '; text-align: center;">';
        $html .= '<div style="background: ' . $headerBg . '; padding: 20px 16px;">';
        $html .= '<div style="color: ' . $headerColor . '; font-size: 16px; font-weight: 600;">' . $title . '</div>';
        $html .= '<div style="color: ' . $headerColor . '; font-size: 12px; margin-top: 8px;"><span style="font-size: 14px;">' . $currency . '</span><span style="font-size: 36px; font-weight: 700;">' . $price . '</span><span style="opacity: 0.7;">' . $period . '</span></div>';
        $html .= '</div>';

        $html .= '<div style="padding: 16px;">';
        foreach (array_slice($features, 0, 5) as $feature) {
            $featureText = htmlspecialchars($feature['text'] ?? '', ENT_QUOTES, 'UTF-8');
            $included = !empty($feature['included']);
            $checkColor = $included ? '#10b981' : '#d1d5db';
            $checkIcon = $included ? '✓' : '✕';
            $textStyle = $included ? 'color: #374151;' : 'color: #9ca3af; text-decoration: line-through;';
            $html .= '<div style="padding: 6px 0; font-size: 12px; display: flex; align-items: center; gap: 6px;">';
            $html .= '<span style="color: ' . $checkColor . '; font-weight: 700;">' . $checkIcon . '</span>';
            $html .= '<span style="' . $textStyle . '">' . $featureText . '</span>';
            $html .= '</div>';
        }
        if ($buttonText) {
            $btnBg = $featured ? $featuredColor : '#fff';
            $btnColor = $featured ? '#fff' : $featuredColor;
            $btnBorder = $featured ? 'none' : '2px solid ' . $featuredColor;
            $html .= '<div style="margin-top: 12px; padding: 10px; background: ' . $btnBg . '; color: ' . $btnColor . '; border: ' . $btnBorder . '; border-radius: 6px; font-weight: 600; font-size: 13px;">' . $buttonText . '</div>';
        }
        $html .= '</div></div>';

        return $html;
    }
}
