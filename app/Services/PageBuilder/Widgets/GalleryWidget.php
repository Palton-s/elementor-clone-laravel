<?php

namespace App\Services\PageBuilder\Widgets;

class GalleryWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'gallery';
        $this->label = 'Gallery';
        $this->icon = '🖼️';
        $this->categories = ['basic', 'media'];
        $this->keywords = ['gallery', 'images', 'photos', 'grid', 'lightbox'];

        $this->defaultSettings = [
            'images' => [],
            'columns' => 3,
            'gap' => 10,
            'layout' => 'grid',
            'show_caption' => false,
            'border_radius' => 4,
            'image_size' => 'cover',
        ];

        $this->controls = [
            'images' => ['type' => 'gallery', 'label' => 'Images'],
            'columns' => ['type' => 'select', 'label' => 'Columns', 'options' => ['1', '2', '3', '4', '5', '6']],
            'gap' => ['type' => 'number', 'label' => 'Gap (px)', 'min' => 0, 'max' => 50],
            'layout' => ['type' => 'select', 'label' => 'Layout', 'options' => ['grid', 'masonry']],
            'show_caption' => ['type' => 'boolean', 'label' => 'Show Captions'],
            'border_radius' => ['type' => 'number', 'label' => 'Border Radius (px)', 'min' => 0, 'max' => 50, 'tab' => 'style'],
            'image_size' => ['type' => 'select', 'label' => 'Image Fit', 'options' => ['cover', 'contain'], 'tab' => 'style'],
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
        $images = $settings['images'] ?? [];
        $columns = (int) $settings['columns'];
        $gap = (int) $settings['gap'];
        $layout = $settings['layout'];
        $showCaption = $settings['show_caption'];
        $borderRadius = (int) $settings['border_radius'];
        $imageSize = $settings['image_size'];

        if (empty($images)) {
            return '<div class="pb-gallery-placeholder" style="text-align:center;padding:2rem;color:#999;background:#f5f5f5;border-radius:8px;">Nenhuma imagem selecionada</div>';
        }

        if ($layout === 'masonry') {
            return $this->renderMasonry($images, $columns, $gap, $showCaption, $borderRadius, $imageSize);
        }

        $gridStyle = "display: grid; grid-template-columns: repeat({$columns}, 1fr); gap: {$gap}px;";

        $items = '';
        foreach ($images as $img) {
            $url = htmlspecialchars($img['url'] ?? '', ENT_QUOTES, 'UTF-8');
            $alt = htmlspecialchars($img['alt'] ?? '', ENT_QUOTES, 'UTF-8');
            $caption = htmlspecialchars($img['caption'] ?? '', ENT_QUOTES, 'UTF-8');

            $imgTag = "<img src=\"{$url}\" alt=\"{$alt}\" style=\"width: 100%; height: 100%; object-fit: {$imageSize}; border-radius: {$borderRadius}px; display: block;\">";

            $item = "<div class=\"pb-gallery-item\" style=\"overflow: hidden; border-radius: {$borderRadius}px;\">{$imgTag}";
            if ($showCaption && $caption) {
                $item .= "<div style=\"padding: 6px 8px; font-size: 12px; color: #666; background: #fff;\">{$caption}</div>";
            }
            $item .= "</div>";
            $items .= $item;
        }

        return "<div class=\"pb-gallery\" style=\"{$gridStyle}\">{$items}</div>";
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $images = $settings['images'] ?? [];
        $columns = (int) $settings['columns'];
        $gap = (int) $settings['gap'];
        $borderRadius = (int) $settings['border_radius'];

        if (empty($images)) {
            return '<div class="pb-gallery-placeholder" style="text-align:center;padding:2rem;color:#999;background:#f5f5f5;border-radius:8px;cursor:pointer;">🖼️ Clique para adicionar imagens</div>';
        }

        $gridStyle = "display: grid; grid-template-columns: repeat({$columns}, 1fr); gap: {$gap}px;";
        $items = '';
        foreach (array_slice($images, 0, 12) as $img) {
            $url = htmlspecialchars($img['url'] ?? '', ENT_QUOTES, 'UTF-8');
            $alt = htmlspecialchars($img['alt'] ?? '', ENT_QUOTES, 'UTF-8');
            $items .= "<div style=\"overflow:hidden;border-radius:{$borderRadius}px;aspect-ratio:1;background:#f1f5f9\"><img src=\"{$url}\" alt=\"{$alt}\" style=\"width:100%;height:100%;object-fit:cover;border-radius:{$borderRadius}px\"></div>";
        }
        if (count($images) > 12) {
            $items .= "<div style=\"display:flex;align-items:center;justify-content:center;aspect-ratio:1;background:#f1f5f9;border-radius:{$borderRadius}px;font-size:.8rem;color:#666\">+" . (count($images) - 12) . " more</div>";
        }

        return "<div class=\"pb-gallery-editor\" style=\"{$gridStyle}\">{$items}</div>";
    }

    private function renderMasonry(array $images, int $columns, int $gap, bool $showCaption, int $borderRadius, string $imageSize): string
    {
        $items = '';
        foreach ($images as $img) {
            $url = htmlspecialchars($img['url'] ?? '', ENT_QUOTES, 'UTF-8');
            $alt = htmlspecialchars($img['alt'] ?? '', ENT_QUOTES, 'UTF-8');
            $caption = htmlspecialchars($img['caption'] ?? '', ENT_QUOTES, 'UTF-8');

            $item = "<div style=\"break-inside: avoid; margin-bottom: {$gap}px; overflow: hidden; border-radius: {$borderRadius}px;\">";
            $item .= "<img src=\"{$url}\" alt=\"{$alt}\" style=\"width: 100%; display: block; border-radius: {$borderRadius}px; object-fit: {$imageSize};\">";
            if ($showCaption && $caption) {
                $item .= "<div style=\"padding: 6px 8px; font-size: 12px; color: #666; background: #fff;\">{$caption}</div>";
            }
            $item .= "</div>";
            $items .= $item;
        }

        return "<div class=\"pb-gallery pb-gallery-masonry\" style=\"column-count: {$columns}; column-gap: {$gap}px;\">{$items}</div>";
    }
}
