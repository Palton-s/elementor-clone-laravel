<?php

namespace App\Services\PageBuilder\Widgets;

class ButtonWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'button';
        $this->label = 'Button';
        $this->icon = 'button-icon';
        $this->categories = ['basic', 'interactive'];
        $this->keywords = ['button', 'link', 'cta', 'click'];

        $this->defaultSettings = [
            'text' => 'Clique aqui',
            'link' => '#',
            'link_target' => '_self',
            'alignment' => 'left',
            'size' => 'medium',
            'full_width' => false,
            'background_color' => '#007bff',
            'background_color_hover' => '#0056b3',
            'text_color' => '#ffffff',
            'text_color_hover' => '#ffffff',
            'border_color' => 'transparent',
            'border_color_hover' => 'transparent',
            'border_radius' => '4px',
            'border_width' => '0px',
            'padding_top_bottom' => '12px',
            'padding_left_right' => '24px',
            'font_size' => '16px',
            'font_weight' => '500',
            'icon' => '',
            'icon_position' => 'left',
            'icon_gap' => '8px',
            'hover_animation' => 'none',
            'background_type' => 'classic',
            'background_gradient' => null,
            'button_text_shadow' => '',
            'css_id' => '',
            'css_classes' => '',
            'scroll_animation' => 'none',
            'position_type' => 'default',
            'position_top' => '',
            'position_right' => '',
            'position_bottom' => '',
            'position_left' => '',
        ];

        $this->controls = [
            'text' => ['type' => 'text', 'label' => 'Button Text', 'required' => true, 'max_length' => 100],
            'link' => ['type' => 'url', 'label' => 'Link'],
            'alignment' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left', 'center', 'right', 'stretch']],
            'size' => ['type' => 'select', 'label' => 'Size', 'options' => ['small', 'medium', 'large', 'xl']],
            'icon' => ['type' => 'icon', 'label' => 'Icon'],
            'icon_position' => ['type' => 'select', 'label' => 'Icon Position', 'options' => ['left', 'right']],
            'background_type' => ['type' => 'select', 'label' => 'Background Type', 'options' => ['classic', 'gradient']],
            'background_color' => ['type' => 'color', 'label' => 'Background Color', 'tab' => 'style'],
            'background_gradient' => ['type' => 'gradient', 'label' => 'Gradient', 'tab' => 'style'],
            'text_color' => ['type' => 'color', 'label' => 'Text Color', 'tab' => 'style'],
            'button_text_shadow' => ['type' => 'text_shadow', 'label' => 'Text Shadow', 'tab' => 'style'],
            'border_radius' => ['type' => 'text', 'label' => 'Border Radius', 'tab' => 'style'],
            'font_size' => ['type' => 'number', 'label' => 'Font Size', 'min' => 10, 'max' => 100, 'tab' => 'style'],
            'typography' => ['type' => 'typography', 'label' => 'Typography', 'tab' => 'style'],
            'border' => ['type' => 'border', 'label' => 'Border', 'tab' => 'style'],
            'box_shadow' => ['type' => 'box_shadow', 'label' => 'Box Shadow', 'tab' => 'style'],
            'hover' => ['type' => 'hover', 'label' => 'Hover Effects', 'tab' => 'style'],
            'dimensions' => ['type' => 'dimensions', 'label' => 'Padding & Margin', 'tab' => 'advanced'],
            'z_index' => ['type' => 'number', 'label' => 'Z-Index', 'tab' => 'advanced'],
            'position_type' => ['type' => 'select', 'label' => 'Position', 'options' => ['default', 'relative', 'absolute', 'fixed', 'sticky'], 'tab' => 'advanced'],
            'position_top' => ['type' => 'text', 'label' => 'Top', 'tab' => 'advanced'],
            'position_right' => ['type' => 'text', 'label' => 'Right', 'tab' => 'advanced'],
            'position_bottom' => ['type' => 'text', 'label' => 'Bottom', 'tab' => 'advanced'],
            'position_left' => ['type' => 'text', 'label' => 'Left', 'tab' => 'advanced'],
            'css_classes' => ['type' => 'text', 'label' => 'CSS Classes', 'tab' => 'advanced'],
            'css_id' => ['type' => 'text', 'label' => 'CSS ID', 'tab' => 'advanced'],
            'custom_css' => ['type' => 'custom_css', 'label' => 'Custom CSS', 'tab' => 'advanced'],
            'animation' => ['type' => 'animation', 'label' => 'Animation', 'tab' => 'advanced'],
            'scroll_animation' => ['type' => 'scroll_animation', 'label' => 'Scroll Animation', 'tab' => 'advanced'],
            'visibility' => ['type' => 'visibility', 'label' => 'Responsive Visibility', 'tab' => 'advanced'],
        ];
    }

    public function render(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $text = htmlspecialchars($settings['text'], ENT_QUOTES, 'UTF-8');
        $link = htmlspecialchars($settings['link'], ENT_QUOTES, 'UTF-8');
        $target = htmlspecialchars($settings['link_target'], ENT_QUOTES, 'UTF-8');
        $alignment = $settings['alignment'];
        $size = $settings['size'];
        $fullWidth = $settings['full_width'];
        $bgColor = $settings['background_color'];
        $bgHover = $settings['background_color_hover'];
        $textColor = $settings['text_color'];
        $textHover = $settings['text_color_hover'];
        $borderColor = $settings['border_color'];
        $borderHover = $settings['border_color_hover'];
        $borderRadius = $settings['border_radius'];
        $borderWidth = $settings['border_width'];
        $paddingTB = $settings['padding_top_bottom'];
        $paddingLR = $settings['padding_left_right'];
        $fontSize = $settings['font_size'];
        $fontWeight = $settings['font_weight'];
        $icon = $settings['icon'];
        $iconPosition = $settings['icon_position'];
        $iconGap = $settings['icon_gap'];

        $sizeMap = [
            'small' => ['padding' => '8px 16px', 'font' => '14px'],
            'medium' => ['padding' => '12px 24px', 'font' => '16px'],
            'large' => ['padding' => '16px 32px', 'font' => '18px'],
            'xl' => ['padding' => '20px 40px', 'font' => '20px'],
        ];

        if (isset($sizeMap[$size])) {
            $paddingTB = explode(' ', $sizeMap[$size]['padding'])[0];
            $paddingLR = explode(' ', $sizeMap[$size]['padding'])[1];
            $fontSize = $sizeMap[$size]['font'];
        }

        $style = "color: {$textColor}; border: {$borderWidth} solid {$borderColor}; border-radius: {$borderRadius}; padding: {$paddingTB} {$paddingLR}; font-size: {$fontSize}; font-weight: {$fontWeight}; cursor: pointer; display: inline-block; text-decoration: none; transition: all 0.3s ease;";

        $bgType = $settings['background_type'] ?? 'classic';
        $bgGradient = $settings['background_gradient'] ?? null;
        if ($bgType === 'gradient' && $bgGradient) {
            $gType = $bgGradient['type'] ?? 'linear';
            $angle = $bgGradient['angle'] ?? 180;
            $c1 = $bgGradient['color1'] ?? '#6366f1';
            $c2 = $bgGradient['color2'] ?? '#8b5cf6';
            $p1 = $bgGradient['position1'] ?? 0;
            $p2 = $bgGradient['position2'] ?? 100;
            if ($gType === 'radial') $style .= " background: radial-gradient(circle,{$c1} {$p1}%,{$c2} {$p2}%);";
            else $style .= " background: linear-gradient({$angle}deg,{$c1} {$p1}%,{$c2} {$p2}%);";
        } else {
            $style .= " background-color: {$bgColor};";
        }

        $textShadow = $settings['button_text_shadow'] ?? '';
        if ($textShadow) $style .= " text-shadow: {$textShadow};";

        $posType = $settings['position_type'] ?? 'default';
        if ($posType !== 'default') {
            $style .= " position: {$posType};";
            if (!empty($settings['position_top'])) $style .= " top: {$settings['position_top']};";
            if (!empty($settings['position_right'])) $style .= " right: {$settings['position_right']};";
            if (!empty($settings['position_bottom'])) $style .= " bottom: {$settings['position_bottom']};";
            if (!empty($settings['position_left'])) $style .= " left: {$settings['position_left']};";
        }
        if (!empty($settings['z_index'])) $style .= " z-index: {$settings['z_index']};";

        if ($fullWidth) {
            $style .= ' width: 100%; text-align: center;';
        }

        $hoverStyle = "background-color: {$bgHover}; color: {$textHover}; border-color: {$borderHover};";

        $iconHtml = '';
        if ($icon) {
            $iconTag = "<i class=\"{$icon}\" style=\"margin-{$iconPosition}: {$iconGap}; vertical-align: middle;\"></i>";
            if ($iconPosition === 'left') {
                $iconHtml = $iconTag;
            } else {
                $iconHtml = $iconTag;
            }
        }

        $buttonContent = '';
        if ($icon && $iconPosition === 'left') {
            $buttonContent = "{$iconHtml}<span>{$text}</span>";
        } elseif ($icon && $iconPosition === 'right') {
            $buttonContent = "<span>{$text}</span>{$iconHtml}";
        } else {
            $buttonContent = $text;
        }

        $hoverData = "data-hover-style=\"{$hoverStyle}\"";
        $scrollAnim = $settings['scroll_animation'] ?? '';
        $scrollData = $scrollAnim && $scrollAnim !== 'none' ? " data-scroll-animation=\"{$scrollAnim}\"" : '';
        $buttonHtml = "<a href=\"{$link}\" target=\"{$target}\" class=\"pb-button pb-button-{$size}\" style=\"{$style}\" {$hoverData}{$scrollData}>{$buttonContent}</a>";

        $hoverStyle = $this->buildHoverStyle("pb-button-{$size}", $styles);

        if ($alignment !== 'stretch') {
            $buttonHtml = "<div style=\"text-align: {$alignment};\">{$buttonHtml}</div>";
        }

        return $hoverStyle . $buttonHtml;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $text = htmlspecialchars($settings['text'], ENT_QUOTES, 'UTF-8');
        $alignment = $settings['alignment'];
        $size = $settings['size'];
        $fullWidth = $settings['full_width'];
        $bgColor = $settings['background_color'];
        $textColor = $settings['text_color'];
        $borderColor = $settings['border_color'];
        $borderRadius = $settings['border_radius'];
        $borderWidth = $settings['border_width'];
        $fontSize = $settings['font_size'];
        $fontWeight = $settings['font_weight'];

        $sizeMap = [
            'small' => ['padding' => '8px 16px', 'font' => '14px'],
            'medium' => ['padding' => '12px 24px', 'font' => '16px'],
            'large' => ['padding' => '16px 32px', 'font' => '18px'],
            'xl' => ['padding' => '20px 40px', 'font' => '20px'],
        ];

        if (isset($sizeMap[$size])) {
            $paddingTB = explode(' ', $sizeMap[$size]['padding'])[0];
            $paddingLR = explode(' ', $sizeMap[$size]['padding'])[1];
            $fontSize = $sizeMap[$size]['font'];
        } else {
            $paddingTB = $settings['padding_top_bottom'];
            $paddingLR = $settings['padding_left_right'];
        }

        $style = "background-color: {$bgColor}; color: {$textColor}; border: {$borderWidth} solid {$borderColor}; border-radius: {$borderRadius}; padding: {$paddingTB} {$paddingLR}; font-size: {$fontSize}; font-weight: {$fontWeight}; cursor: pointer; display: inline-block; text-decoration: none;";

        if ($fullWidth) {
            $style .= ' width: 100%; text-align: center;';
        }

        $hoverClass = "pb-button-{$size}-editor";
        $buttonHtml = "<button class=\"pb-button-editor {$hoverClass}\" style=\"{$style}\">{$text}</button>";

        $hoverStyle = $this->buildHoverStyle($hoverClass, $styles);

        if ($alignment !== 'stretch') {
            $buttonHtml = "<div style=\"text-align: {$alignment};\">{$buttonHtml}</div>";
        }

        return $hoverStyle . $buttonHtml;
    }
}
