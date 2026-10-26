<?php

namespace App\Services\PageBuilder\Widgets;

class TestimonialWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'testimonial';
        $this->label = 'Testimonial';
        $this->icon = '💬';
        $this->categories = ['general', 'social'];
        $this->keywords = ['testimonial', 'review', 'quote', 'feedback'];

        $this->defaultSettings = [
            'content' => 'Este produto transformou completamente a nossa rotina. A equipe é muito atenciosa e os recursos são exatamente o que precisávamos.',
            'name' => 'Maria Silva',
            'position' => 'CEO',
            'company' => 'Empresa',
            'avatar' => [],
            'rating' => 5,
            'alignment' => 'center',
            'text_color' => '#4b5563',
            'name_color' => '#1f2937',
        ];

        $this->controls = [
            'content' => ['type' => 'wysiwyg', 'label' => 'Testimonial Content'],
            'name' => ['type' => 'text', 'label' => 'Name', 'required' => true, 'max_length' => 100],
            'position' => ['type' => 'text', 'label' => 'Position', 'max_length' => 100],
            'company' => ['type' => 'text', 'label' => 'Company', 'max_length' => 100],
            'avatar' => ['type' => 'image', 'label' => 'Avatar'],
            'rating' => ['type' => 'number', 'label' => 'Rating (0-5)', 'min' => 0, 'max' => 5],
            'alignment' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left', 'center', 'right']],
            'text_color' => ['type' => 'color', 'label' => 'Text Color', 'tab' => 'style'],
            'name_color' => ['type' => 'color', 'label' => 'Name Color', 'tab' => 'style'],
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
        $testimonialContent = $settings['content'];
        $name = htmlspecialchars($settings['name'], ENT_QUOTES, 'UTF-8');
        $position = htmlspecialchars($settings['position'], ENT_QUOTES, 'UTF-8');
        $company = htmlspecialchars($settings['company'], ENT_QUOTES, 'UTF-8');
        $avatar = $settings['avatar'] ?? [];
        $rating = min(5, max(0, (int) $settings['rating']));
        $alignment = $settings['alignment'];
        $textColor = htmlspecialchars($settings['text_color'], ENT_QUOTES, 'UTF-8');
        $nameColor = htmlspecialchars($settings['name_color'], ENT_QUOTES, 'UTF-8');

        $avatarUrl = htmlspecialchars($avatar['url'] ?? '', ENT_QUOTES, 'UTF-8');
        $avatarAlt = htmlspecialchars($avatar['alt'] ?? $name, ENT_QUOTES, 'UTF-8');

        $html = '<div class="pb-testimonial" style="text-align: ' . $alignment . '; padding: 32px; border-radius: 12px; background: #fff; border: 1px solid #e5e7eb;">';

        // Stars
        if ($rating > 0) {
            $html .= '<div class="pb-testimonial-rating" style="margin-bottom: 16px;">';
            for ($i = 1; $i <= 5; $i++) {
                $starColor = $i <= $rating ? '#f59e0b' : '#d1d5db';
                $html .= '<span style="color: ' . $starColor . '; font-size: 20px; margin: 0 2px;">★</span>';
            }
            $html .= '</div>';
        }

        // Quote content
        $html .= '<div class="pb-testimonial-content" style="color: ' . $textColor . '; font-size: 16px; line-height: 1.7; font-style: italic; margin-bottom: 24px;">';
        $html .= '<span style="font-size: 48px; line-height: 0; vertical-align: -0.3em; color: #d1d5db; margin-right: 8px;">"</span>';
        $html .= $testimonialContent;
        $html .= '<span style="font-size: 48px; line-height: 0; vertical-align: -0.3em; color: #d1d5db; margin-left: 8px;">"</span>';
        $html .= '</div>';

        // Avatar + Author
        $html .= '<div class="pb-testimonial-author" style="display: flex; align-items: center; gap: 12px; ' . ($alignment === 'center' ? 'justify-content: center;' : '') . '">';
        if ($avatarUrl) {
            $html .= '<img src="' . $avatarUrl . '" alt="' . $avatarAlt . '" style="width: 56px; height: 56px; border-radius: 50%; object-fit: cover;">';
        } else {
            $html .= '<div style="width: 56px; height: 56px; border-radius: 50%; background: #e5e7eb; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 24px;">👤</div>';
        }
        $html .= '<div>';
        $html .= '<div class="pb-testimonial-name" style="color: ' . $nameColor . '; font-weight: 600; font-size: 16px;">' . $name . '</div>';
        if ($position || $company) {
            $authorLine = $position;
            if ($position && $company) {
                $authorLine .= ' at ';
            }
            $authorLine .= $company;
            $html .= '<div class="pb-testimonial-position" style="color: #9ca3af; font-size: 14px;">' . $authorLine . '</div>';
        }
        $html .= '</div>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $testimonialContent = strip_tags($settings['content'] ?? '');
        $name = htmlspecialchars($settings['name'], ENT_QUOTES, 'UTF-8');
        $position = htmlspecialchars($settings['position'], ENT_QUOTES, 'UTF-8');
        $company = htmlspecialchars($settings['company'], ENT_QUOTES, 'UTF-8');
        $avatar = $settings['avatar'] ?? [];
        $rating = min(5, max(0, (int) $settings['rating']));
        $alignment = $settings['alignment'];
        $textColor = htmlspecialchars($settings['text_color'], ENT_QUOTES, 'UTF-8');
        $nameColor = htmlspecialchars($settings['name_color'], ENT_QUOTES, 'UTF-8');

        $avatarUrl = htmlspecialchars($avatar['url'] ?? '', ENT_QUOTES, 'UTF-8');

        $html = '<div style="text-align: ' . $alignment . '; padding: 20px; border-radius: 8px; border: 1px solid #e5e7eb;">';

        if ($rating > 0) {
            $html .= '<div style="margin-bottom: 8px;">';
            for ($i = 1; $i <= 5; $i++) {
                $starColor = $i <= $rating ? '#f59e0b' : '#d1d5db';
                $html .= '<span style="color: ' . $starColor . '; font-size: 16px;">★</span>';
            }
            $html .= '</div>';
        }

        $truncatedContent = mb_strlen($testimonialContent) > 120 ? mb_substr($testimonialContent, 0, 120) . '...' : $testimonialContent;
        $html .= '<div style="color: ' . $textColor . '; font-size: 13px; font-style: italic; margin-bottom: 12px; line-height: 1.5;">"' . htmlspecialchars($truncatedContent, ENT_QUOTES, 'UTF-8') . '"</div>';

        $html .= '<div style="display: flex; align-items: center; gap: 8px; ' . ($alignment === 'center' ? 'justify-content: center;' : '') . '">';
        if ($avatarUrl) {
            $html .= '<img src="' . $avatarUrl . '" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">';
        } else {
            $html .= '<div style="width: 40px; height: 40px; border-radius: 50%; background: #e5e7eb; display: flex; align-items: center; justify-content: center; font-size: 18px;">👤</div>';
        }
        $html .= '<div>';
        $html .= '<div style="color: ' . $nameColor . '; font-weight: 600; font-size: 13px;">' . $name . '</div>';
        if ($position || $company) {
            $html .= '<div style="color: #9ca3af; font-size: 11px;">' . $position . ($position && $company ? ' at ' : '') . $company . '</div>';
        }
        $html .= '</div>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }
}
