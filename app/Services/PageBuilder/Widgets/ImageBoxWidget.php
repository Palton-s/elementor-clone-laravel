<?php

namespace App\Services\PageBuilder\Widgets;

class ImageBoxWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'image_box';
        $this->label = 'Image Box';
        $this->icon = '🖼';
        $this->categories = ['general', 'media'];
        $this->keywords = ['image', 'box', 'figure', 'description'];

        $this->defaultSettings = [
            'image' => [],
            'title' => 'Título da imagem',
            'description' => 'Escreva aqui uma breve descrição desta imagem.',
            'title_tag' => 'h3',
            'image_width' => '100%',
            'title_color' => '#1f2937',
            'description_color' => '#6b7280',
            'alignment' => 'left',
            'border_radius' => '8px',
        ];

        $this->controls = [
            'image' => ['type' => 'image', 'label' => 'Image'],
            'title' => ['type' => 'text', 'label' => 'Title', 'max_length' => 200],
            'description' => ['type' => 'wysiwyg', 'label' => 'Description'],
            'title_tag' => ['type' => 'select', 'label' => 'Title Tag', 'options' => ['h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div']],
            'image_width' => ['type' => 'text', 'label' => 'Image Width'],
            'alignment' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left', 'center', 'right']],
            'title_color' => ['type' => 'color', 'label' => 'Title Color', 'tab' => 'style'],
            'description_color' => ['type' => 'color', 'label' => 'Description Color', 'tab' => 'style'],
            'border_radius' => ['type' => 'text', 'label' => 'Image Border Radius', 'tab' => 'style'],
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
        $image = $settings['image'] ?? [];
        $title = htmlspecialchars($settings['title'], ENT_QUOTES, 'UTF-8');
        $description = $settings['description'];
        $titleTag = htmlspecialchars($settings['title_tag'], ENT_QUOTES, 'UTF-8');
        $imageWidth = htmlspecialchars($settings['image_width'], ENT_QUOTES, 'UTF-8');
        $titleColor = htmlspecialchars($settings['title_color'], ENT_QUOTES, 'UTF-8');
        $descriptionColor = htmlspecialchars($settings['description_color'], ENT_QUOTES, 'UTF-8');
        $alignment = $settings['alignment'];
        $borderRadius = htmlspecialchars($settings['border_radius'], ENT_QUOTES, 'UTF-8');

        $imageUrl = htmlspecialchars($image['url'] ?? '', ENT_QUOTES, 'UTF-8');
        $imageAlt = htmlspecialchars($image['alt'] ?? '', ENT_QUOTES, 'UTF-8');

        $html = '<figure class="pb-image-box" style="text-align: ' . $alignment . '; margin: 0;">';

        if ($imageUrl) {
            $html .= '<img src="' . $imageUrl . '" alt="' . $imageAlt . '" style="width: ' . $imageWidth . '; border-radius: ' . $borderRadius . '; display: block; max-width: 100%;">';
        } else {
            $html .= '<div style="width: ' . $imageWidth . '; height: 200px; background: #e5e7eb; border-radius: ' . $borderRadius . '; display: flex; align-items: center; justify-content: center; color: #9ca3af;">Nenhuma imagem selecionada</div>';
        }

        $html .= '<figcaption style="padding: 16px 0 0 0;">';
        if ($title) {
            $html .= '<' . $titleTag . ' class="pb-image-box-title" style="color: ' . $titleColor . '; font-size: 20px; font-weight: 600; margin: 0 0 8px 0;">' . $title . '</' . $titleTag . '>';
        }
        if ($description) {
            $html .= '<div class="pb-image-box-description" style="color: ' . $descriptionColor . '; font-size: 15px; line-height: 1.6;">' . $description . '</div>';
        }
        $html .= '</figcaption>';
        $html .= '</figure>';

        return $html;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $image = $settings['image'] ?? [];
        $title = htmlspecialchars($settings['title'], ENT_QUOTES, 'UTF-8');
        $description = strip_tags($settings['description'] ?? '');
        $titleTag = htmlspecialchars($settings['title_tag'], ENT_QUOTES, 'UTF-8');
        $imageWidth = htmlspecialchars($settings['image_width'], ENT_QUOTES, 'UTF-8');
        $titleColor = htmlspecialchars($settings['title_color'], ENT_QUOTES, 'UTF-8');
        $descriptionColor = htmlspecialchars($settings['description_color'], ENT_QUOTES, 'UTF-8');
        $alignment = $settings['alignment'];
        $borderRadius = htmlspecialchars($settings['border_radius'], ENT_QUOTES, 'UTF-8');

        $imageUrl = htmlspecialchars($image['url'] ?? '', ENT_QUOTES, 'UTF-8');
        $imageAlt = htmlspecialchars($image['alt'] ?? '', ENT_QUOTES, 'UTF-8');

        $html = '<div style="text-align: ' . $alignment . '; padding: 8px 0;">';

        if ($imageUrl) {
            $html .= '<img src="' . $imageUrl . '" alt="' . $imageAlt . '" style="width: ' . $imageWidth . '; border-radius: ' . $borderRadius . '; max-width: 100%;">';
        } else {
            $html .= '<div style="width: ' . $imageWidth . '; height: 160px; background: #e5e7eb; border-radius: ' . $borderRadius . '; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 14px;">Nenhuma imagem selecionada</div>';
        }

        if ($title) {
            $html .= '<div style="color: ' . $titleColor . '; font-size: 16px; font-weight: 600; margin-top: 8px;">' . $title . '</div>';
        }
        if ($description) {
            $html .= '<div style="color: ' . $descriptionColor . '; font-size: 13px; line-height: 1.5; margin-top: 4px;">' . htmlspecialchars(mb_substr($description, 0, 100), ENT_QUOTES, 'UTF-8') . (mb_strlen($description) > 100 ? '...' : '') . '</div>';
        }
        $html .= '</div>';

        return $html;
    }
}
