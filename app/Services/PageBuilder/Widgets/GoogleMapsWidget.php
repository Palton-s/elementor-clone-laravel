<?php

namespace App\Services\PageBuilder\Widgets;

class GoogleMapsWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'google_maps';
        $this->label = 'Google Maps';
        $this->icon = '🗺️';
        $this->categories = ['general'];
        $this->keywords = ['map', 'location', 'address', 'google', 'openstreetmap'];

        $this->defaultSettings = [
            'address' => 'Av. Paulista, 1578 - Bela Vista, São Paulo',
            'latitude' => '-23.5632',
            'longitude' => '-46.6542',
            'zoom' => 15,
            'height' => '400px',
            'map_type' => 'roadmap',
            'border_radius' => '8px',
        ];

        $this->controls = [
            'address' => ['type' => 'text', 'label' => 'Address', 'max_length' => 500],
            'latitude' => ['type' => 'text', 'label' => 'Latitude'],
            'longitude' => ['type' => 'text', 'label' => 'Longitude'],
            'zoom' => ['type' => 'number', 'label' => 'Zoom Level', 'min' => 1, 'max' => 19],
            'height' => ['type' => 'text', 'label' => 'Height'],
            'map_type' => ['type' => 'select', 'label' => 'Map Type', 'options' => ['roadmap', 'satellite', 'terrain', 'hybrid']],
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
        // A cleared or mistyped coordinate must not break the whole page: fall back to the defaults
        $latitude = is_numeric($settings['latitude']) ? (float) $settings['latitude'] : (float) $this->defaultSettings['latitude'];
        $longitude = is_numeric($settings['longitude']) ? (float) $settings['longitude'] : (float) $this->defaultSettings['longitude'];
        $zoom = (int) $settings['zoom'];
        $height = htmlspecialchars($settings['height'], ENT_QUOTES, 'UTF-8');
        $mapType = htmlspecialchars($settings['map_type'], ENT_QUOTES, 'UTF-8');
        $borderRadius = htmlspecialchars($settings['border_radius'], ENT_QUOTES, 'UTF-8');

        $osmLayer = $mapType === 'satellite' ? '/sat/' : ($mapType === 'terrain' ? '/transport/' : '');

        $mapUrl = 'https://www.openstreetmap.org/export/embed.html?bbox='
            . ($longitude - 0.01) . ',' . ($latitude - 0.01) . ','
            . ($longitude + 0.01) . ',' . ($latitude + 0.01)
            . '&layer=' . $mapType
            . '&marker=' . $latitude . ',' . $longitude;

        $mapUrl = htmlspecialchars($mapUrl, ENT_QUOTES, 'UTF-8');

        $html = '<div class="pb-google-maps" style="width: 100%; border-radius: ' . $borderRadius . '; overflow: hidden;">';
        $html .= '<iframe src="' . $mapUrl . '" width="100%" height="' . $height . '" style="border: 0; width: 100%; display: block;" loading="lazy" allowfullscreen></iframe>';
        $html .= '</div>';

        return $html;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $latitude = is_numeric($settings['latitude']) ? (float) $settings['latitude'] : (float) $this->defaultSettings['latitude'];
        $longitude = is_numeric($settings['longitude']) ? (float) $settings['longitude'] : (float) $this->defaultSettings['longitude'];
        $height = htmlspecialchars($settings['height'], ENT_QUOTES, 'UTF-8');
        $borderRadius = htmlspecialchars($settings['border_radius'], ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars($settings['address'], ENT_QUOTES, 'UTF-8');

        $mapUrl = 'https://www.openstreetmap.org/export/embed.html?bbox='
            . ($longitude - 0.01) . ',' . ($latitude - 0.01) . ','
            . ($longitude + 0.01) . ',' . ($latitude + 0.01)
            . '&layer=map&marker=' . $latitude . ',' . $longitude;

        $mapUrl = htmlspecialchars($mapUrl, ENT_QUOTES, 'UTF-8');

        $html = '<div style="border-radius: ' . $borderRadius . '; overflow: hidden;">';
        $html .= '<iframe src="' . $mapUrl . '" width="100%" height="' . $height . '" style="border: 0; display: block;" loading="lazy"></iframe>';
        if ($address) {
            $html .= '<div style="padding: 8px 12px; background: #f9fafb; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb;">📍 ' . $address . '</div>';
        }
        $html .= '</div>';

        return $html;
    }
}
