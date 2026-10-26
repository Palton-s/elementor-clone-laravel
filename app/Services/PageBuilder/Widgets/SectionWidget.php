<?php

namespace App\Services\PageBuilder\Widgets;

use App\Services\PageBuilder\Core\Renderer;

class SectionWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'section';
        $this->label = 'Section';
        $this->icon = 'section-icon';
        $this->categories = ['layout', 'structure'];
        $this->container = true;
        $this->keywords = ['section', 'wrapper', 'container', 'layout', 'row'];

        $this->defaultSettings = [
            'layout' => 'boxed',
            'content_width' => '1140px',
            'min_height' => 'auto',
            'gap' => 'default',
            'flex_wrap' => 'wrap',
            'align_items' => 'stretch',
            'justify_content' => 'flex-start',
            'background_type' => 'none',
            'background_color' => 'transparent',
            'background_image' => [],
            'background_gradient' => ['type' => 'linear', 'angle' => 180, 'color1' => '#6366f1', 'color2' => '#8b5cf6', 'position1' => 0, 'position2' => 100],
            'background_overlay_color' => '#ffffff',
            'background_overlay_opacity' => 0,
            'background_overlay_blend' => 'normal',
            'background_position' => 'center center',
            'background_size' => 'cover',
            'background_repeat' => 'no-repeat',
            'shape_divider_top' => 'none',
            'shape_divider_top_color' => '#ffffff',
            'shape_divider_top_height' => '100',
            'shape_divider_bottom' => 'none',
            'shape_divider_bottom_color' => '#ffffff',
            'shape_divider_bottom_height' => '100',
            'padding_top' => '40px',
            'padding_bottom' => '40px',
            'padding_left' => '0px',
            'padding_right' => '0px',
            'margin_top' => '10px',
            'margin_bottom' => '10px',
            'border_radius' => '0px',
            'box_shadow' => 'none',
            'z_index' => 'auto',
            'css_id' => '',
            'css_classes' => '',
            'parallax' => false,
            'parallax_speed' => 0.5,
            'video_background' => '',
            'video_bg_loop' => true,
            'video_bg_mute' => true,
            'position_type' => 'default',
            'position_top' => 'auto',
            'position_right' => 'auto',
            'position_bottom' => 'auto',
            'position_left' => 'auto',
        ];

        $this->controls = [
            'layout' => ['type' => 'select', 'label' => 'Layout', 'options' => ['boxed', 'full_width', 'full_height']],
            'content_width' => ['type' => 'text', 'label' => 'Content Width'],
            'min_height' => ['type' => 'text', 'label' => 'Min Height'],
            'align_items' => ['type' => 'select', 'label' => 'Align Items', 'options' => ['stretch', 'flex-start', 'center', 'flex-end']],
            'justify_content' => ['type' => 'select', 'label' => 'Justify Content', 'options' => ['flex-start', 'center', 'flex-end', 'space-between', 'space-around', 'space-evenly']],
            'background_type' => ['type' => 'select', 'label' => 'Background Type', 'options' => ['none', 'classic', 'gradient', 'video']],
            'background_color' => ['type' => 'color', 'label' => 'Background Color', 'tab' => 'style', 'section' => 'Background'],
            'background_gradient' => ['type' => 'gradient', 'label' => 'Gradient', 'tab' => 'style', 'section' => 'Background'],
            'video_background' => ['type' => 'video', 'label' => 'Background Video', 'tab' => 'style', 'section' => 'Background'],
            'video_bg_loop' => ['type' => 'boolean', 'label' => 'Loop', 'tab' => 'style', 'section' => 'Background'],
            'video_bg_mute' => ['type' => 'boolean', 'label' => 'Mute', 'tab' => 'style', 'section' => 'Background'],
            'background_overlay_color' => ['type' => 'color', 'label' => 'Overlay Color', 'tab' => 'style', 'section' => 'Background Overlay'],
            'background_overlay_opacity' => ['type' => 'number', 'label' => 'Overlay Opacity (%)', 'tab' => 'style', 'section' => 'Background Overlay', 'min' => 0, 'max' => 100],
            'background_overlay_blend' => ['type' => 'select', 'label' => 'Blend Mode', 'tab' => 'style', 'section' => 'Background Overlay', 'options' => ['normal', 'multiply', 'screen', 'overlay', 'darken', 'lighten', 'color-dodge', 'color-burn', 'hard-light', 'soft-light', 'difference', 'exclusion', 'hue', 'saturation', 'color', 'luminosity']],
            'shape_divider_top' => ['type' => 'select', 'label' => 'Top Divider', 'tab' => 'style', 'section' => 'Shape Dividers', 'options' => ['none', 'tilt', 'waves', 'mountains', 'clouds', 'triangles', 'drip', 'clouds-dramatic', 'tilt-opacity', 'mountains-peak']],
            'shape_divider_top_color' => ['type' => 'color', 'label' => 'Top Color', 'tab' => 'style', 'section' => 'Shape Dividers'],
            'shape_divider_top_height' => ['type' => 'text', 'label' => 'Top Height (px)', 'tab' => 'style', 'section' => 'Shape Dividers'],
            'shape_divider_bottom' => ['type' => 'select', 'label' => 'Bottom Divider', 'tab' => 'style', 'section' => 'Shape Dividers', 'options' => ['none', 'tilt', 'waves', 'mountains', 'clouds', 'triangles', 'drip', 'clouds-dramatic', 'tilt-opacity', 'mountains-peak']],
            'shape_divider_bottom_color' => ['type' => 'color', 'label' => 'Bottom Color', 'tab' => 'style', 'section' => 'Shape Dividers'],
            'shape_divider_bottom_height' => ['type' => 'text', 'label' => 'Bottom Height (px)', 'tab' => 'style', 'section' => 'Shape Dividers'],
            'border' => ['type' => 'border', 'label' => 'Border', 'tab' => 'style'],
            'box_shadow' => ['type' => 'box_shadow', 'label' => 'Box Shadow', 'tab' => 'style'],
            'padding_top' => ['type' => 'text', 'label' => 'Padding Top', 'tab' => 'advanced'],
            'padding_bottom' => ['type' => 'text', 'label' => 'Padding Bottom', 'tab' => 'advanced'],
            'padding_left' => ['type' => 'text', 'label' => 'Padding Left', 'tab' => 'advanced'],
            'padding_right' => ['type' => 'text', 'label' => 'Padding Right', 'tab' => 'advanced'],
            'margin_top' => ['type' => 'text', 'label' => 'Margin Top', 'tab' => 'advanced'],
            'margin_bottom' => ['type' => 'text', 'label' => 'Margin Bottom', 'tab' => 'advanced'],
            'css_id' => ['type' => 'text', 'label' => 'CSS ID', 'tab' => 'advanced'],
            'css_classes' => ['type' => 'text', 'label' => 'CSS Classes', 'tab' => 'advanced'],
            'z_index' => ['type' => 'number', 'label' => 'Z-Index', 'tab' => 'advanced'],
            'position_type' => ['type' => 'select', 'label' => 'Position', 'options' => ['default', 'relative', 'absolute', 'fixed', 'sticky'], 'tab' => 'advanced', 'section' => 'Position'],
            'sticky_offset' => ['type' => 'text', 'label' => 'Sticky Offset', 'tab' => 'advanced', 'section' => 'Position'],
            'sticky_position' => ['type' => 'select', 'label' => 'Sticky To', 'options' => ['top' => 'Top', 'bottom' => 'Bottom'], 'tab' => 'advanced', 'section' => 'Position'],
            'position_top' => ['type' => 'text', 'label' => 'Top', 'tab' => 'advanced', 'section' => 'Position'],
            'position_right' => ['type' => 'text', 'label' => 'Right', 'tab' => 'advanced', 'section' => 'Position'],
            'position_bottom' => ['type' => 'text', 'label' => 'Bottom', 'tab' => 'advanced', 'section' => 'Position'],
            'position_left' => ['type' => 'text', 'label' => 'Left', 'tab' => 'advanced', 'section' => 'Position'],
            'scroll_animation' => ['type' => 'scroll_animation', 'label' => 'Scroll Animation', 'tab' => 'advanced'],
            'custom_css' => ['type' => 'custom_css', 'label' => 'Custom CSS', 'tab' => 'advanced'],
            'animation' => ['type' => 'animation', 'label' => 'Animation', 'tab' => 'advanced'],
            'visibility' => ['type' => 'visibility', 'label' => 'Responsive Visibility', 'tab' => 'advanced'],
        ];
    }

    public function render(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $children = $content['children'] ?? '';
        $layout = $settings['layout'];
        $contentWidth = $settings['content_width'];
        $minHeight = $settings['min_height'];
        $bgColor = $settings['background_color'];
        $bgImage = $settings['background_image'];
        $bgGradient = $settings['background_gradient'];
        $bgPosition = $settings['background_position'];
        $bgSize = $settings['background_size'];
        $bgRepeat = $settings['background_repeat'];
        $paddingTop = Renderer::cssLength('padding-top', (string) $settings['padding_top']);
        $paddingBottom = Renderer::cssLength('padding-bottom', (string) $settings['padding_bottom']);
        $paddingLeft = Renderer::cssLength('padding-left', (string) $settings['padding_left']);
        $paddingRight = Renderer::cssLength('padding-right', (string) $settings['padding_right']);
        $marginTop = Renderer::cssLength('margin-top', (string) $settings['margin_top']);
        $marginBottom = Renderer::cssLength('margin-bottom', (string) $settings['margin_bottom']);
        $borderRadius = $settings['border_radius'];
        $zIndex = $settings['z_index'];
        $parallax = $settings['parallax'];
        $cssClasses = $settings['css_classes'];
        $positionType = $settings['position_type'] ?? 'default';

        $sectionStyle = "padding: {$paddingTop} {$paddingRight} {$paddingBottom} {$paddingLeft}; margin: {$marginTop} 0 {$marginBottom} 0; border-radius: {$borderRadius};";

        $responsiveCss = '';
        $responsiveMap = [
            'padding_top' => 'padding-top',
            'padding_bottom' => 'padding-bottom',
            'padding_left' => 'padding-left',
            'padding_right' => 'padding-right',
            'margin_top' => 'margin-top',
            'margin_bottom' => 'margin-bottom',
        ];
        $breakpoints = ['tablet' => '768px', 'mobile' => '375px'];
        foreach ($breakpoints as $bp => $width) {
            $bpStyles = [];
            foreach ($responsiveMap as $key => $prop) {
                $respVal = $settings[$key . '_' . $bp] ?? '';
                if ($respVal !== '') {
                    $bpStyles[] = "{$prop}: {$respVal}";
                }
            }
            $fontSize = $settings['font_size_' . $bp] ?? '';
            if ($fontSize !== '') $bpStyles[] = "font-size: {$fontSize}";
            if (!empty($bpStyles)) {
                $responsiveCss .= "@media (max-width: {$width}) { [data-el-id=\"{$settings['_id']}\"] > .pb-section-editor { " . implode('; ', $bpStyles) . "; } } ";
            }
        }

        if ($zIndex !== 'auto') {
            $sectionStyle .= " z-index: {$zIndex};";
        }

        $sectionStyle .= " position: relative; overflow: hidden;";

        $backgroundStyle = '';
        $bgType = $settings['background_type'] ?? 'none';
        if ($bgType === 'classic') {
            if ($bgColor && $bgColor !== 'transparent') {
                $backgroundStyle .= " background-color: {$bgColor};";
            }
            if (!empty($bgImage['url'])) {
                $backgroundStyle .= " background-image: url('{$bgImage['url']}'); background-position: {$bgPosition}; background-size: {$bgSize}; background-repeat: {$bgRepeat};";
            }
        } elseif ($bgType === 'gradient' && $bgGradient) {
            $gradType = $bgGradient['type'] ?? 'linear';
            $angle = $bgGradient['angle'] ?? 180;
            $c1 = $bgGradient['color1'] ?? '#6366f1';
            $c2 = $bgGradient['color2'] ?? '#8b5cf6';
            $p1 = $bgGradient['position1'] ?? 0;
            $p2 = $bgGradient['position2'] ?? 100;
            if ($gradType === 'radial') {
                $backgroundStyle .= " background: radial-gradient(circle, {$c1} {$p1}%, {$c2} {$p2}%);";
            } else {
                $backgroundStyle .= " background: linear-gradient({$angle}deg, {$c1} {$p1}%, {$c2} {$p2}%);";
            }
        }

        $sectionClass = "pb-section pb-section-{$layout}";

        if ($cssClasses) {
            $sectionClass .= " {$cssClasses}";
        }

        if ($parallax) {
            $sectionClass .= ' pb-parallax';
        }

        $innerStyle = '';
        if ($layout === 'boxed') {
            $innerStyle = "max-width: {$contentWidth}; margin: 0 auto;";
        } elseif ($layout === 'full_height') {
            $sectionStyle .= " min-height: 100vh; display: flex; align-items: center;";
        }

        if ($minHeight && $minHeight !== 'auto') {
            $sectionStyle .= " min-height: {$minHeight};";
        }

        if ($positionType && $positionType !== 'default') {
            $sectionStyle .= " position: {$positionType};";
            if ($positionType === 'sticky') {
                $stickyOffset = $settings['sticky_offset'] ?? '0px';
                $stickyPos = $settings['sticky_position'] ?? 'top';
                $sectionStyle .= " {$stickyPos}: {$stickyOffset};";
                $sectionStyle .= " z-index: 100;";
            } else {
                $posTop = $settings['position_top'] ?? 'auto';
                $posRight = $settings['position_right'] ?? 'auto';
                $posBottom = $settings['position_bottom'] ?? 'auto';
                $posLeft = $settings['position_left'] ?? 'auto';
                if ($posTop !== 'auto') $sectionStyle .= " top: {$posTop};";
                if ($posRight !== 'auto') $sectionStyle .= " right: {$posRight};";
                if ($posBottom !== 'auto') $sectionStyle .= " bottom: {$posBottom};";
                if ($posLeft !== 'auto') $sectionStyle .= " left: {$posLeft};";
            }
        }

        $overlayHtml = $this->renderBackgroundOverlay($settings);

        $videoHtml = '';
        if ($bgType === 'video' && !empty($settings['video_background'])) {
            $videoUrl = htmlspecialchars($settings['video_background'], ENT_QUOTES, 'UTF-8');
            $loop = $settings['video_bg_loop'] ? 'loop' : '';
            $mute = $settings['video_bg_mute'] ? 'muted' : '';
            $autoplay = 'autoplay';
            $playsinline = 'playsinline';
            $videoHtml = <<<HTML
<video class="pb-section-video" {$autoplay} {$loop} {$mute} {$playsinline} style="position:absolute;top:50%;left:50%;min-width:100%;min-height:100%;width:auto;height:auto;transform:translate(-50%,-50%);object-fit:cover;z-index:0;" src="{$videoUrl}"></video>
HTML;
        }

        $parallaxAttrs = '';
        if ($parallax) {
            $speed = $settings['parallax_speed'] ?? 0.5;
            $parallaxAttrs = " data-parallax-speed=\"{$speed}\"";
        }

        $gap = $settings['gap'] === 'default' ? '0px' : $settings['gap'];
        $flexWrap = $settings['flex_wrap'];
        $alignItems = $settings['align_items'];
        $justifyContent = $settings['justify_content'];
        $innerStyle .= " display: flex; flex-wrap: {$flexWrap}; align-items: {$alignItems}; justify-content: {$justifyContent}; gap: {$gap}; position: relative; z-index: 2;";

        $shapeTopHtml = $this->renderShapeDivider('top', $settings);
        $shapeBottomHtml = $this->renderShapeDivider('bottom', $settings);

        $responsiveTag = $responsiveCss ? "<style>{$responsiveCss}</style>" : '';

        return <<<HTML
<section class="{$sectionClass}" style="{$sectionStyle}{$backgroundStyle}"{$parallaxAttrs}>
    {$responsiveTag}
    {$videoHtml}
    {$overlayHtml}
    {$shapeTopHtml}
    <div class="pb-section-inner" style="{$innerStyle}">
        {$children}
    </div>
    {$shapeBottomHtml}
</section>
HTML;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $children = $content['children'] ?? '';
        $layout = $settings['layout'];
        $bgColor = $settings['background_color'];
        $bgImage = $settings['background_image'];
        $paddingTop = Renderer::cssLength('padding-top', (string) $settings['padding_top']);
        $paddingBottom = Renderer::cssLength('padding-bottom', (string) $settings['padding_bottom']);
        $paddingLeft = Renderer::cssLength('padding-left', (string) $settings['padding_left']);
        $paddingRight = Renderer::cssLength('padding-right', (string) $settings['padding_right']);
        $marginTop = Renderer::cssLength('margin-top', (string) $settings['margin_top']);
        $marginBottom = Renderer::cssLength('margin-bottom', (string) $settings['margin_bottom']);
        $borderRadius = $settings['border_radius'];
        $contentWidth = $settings['content_width'];

        $sectionStyle = "padding: {$paddingTop} {$paddingRight} {$paddingBottom} {$paddingLeft}; margin: {$marginTop} 0 {$marginBottom} 0; border-radius: {$borderRadius}; position: relative; overflow: hidden;";

        if ($bgColor && $bgColor !== 'transparent') {
            $sectionStyle .= " background-color: {$bgColor};";
        }

        if (!empty($bgImage['url'])) {
            $sectionStyle .= " background-image: url('{$bgImage['url']}'); background-position: center center; background-size: cover; background-repeat: no-repeat;";
        }

        $innerStyle = '';
        if ($layout === 'boxed') {
            $innerStyle = "max-width: {$contentWidth}; margin: 0 auto;";
        }

        $overlayHtml = $this->renderBackgroundOverlay($settings);
        $shapeTopHtml = $this->renderShapeDivider('top', $settings);
        $shapeBottomHtml = $this->renderShapeDivider('bottom', $settings);

        return <<<HTML
<div class="pb-section-editor" style="{$sectionStyle}">
    {$overlayHtml}
    {$shapeTopHtml}
    <div class="pb-section-header">Seção</div>
    <div class="pb-section-content" style="{$innerStyle}">
        {$children}
    </div>
    {$shapeBottomHtml}
</div>
HTML;
    }

    protected function renderBackgroundOverlay(array $settings): string
    {
        $color = $settings['background_overlay_color'] ?? '#000000';
        $opacity = (int) ($settings['background_overlay_opacity'] ?? 0);
        $blend = $settings['background_overlay_blend'] ?? 'normal';

        if ($opacity <= 0) {
            return '';
        }

        $alpha = $opacity / 100;
        $rgb = $this->hexToRgb($color);

        return <<<HTML
<div class="pb-section-overlay" style="position:absolute;top:0;left:0;right:0;bottom:0;background-color:rgba({$rgb['r']},{$rgb['g']},{$rgb['b']},{$alpha});mix-blend-mode:{$blend};pointer-events:none;z-index:1;"></div>
HTML;
    }

    protected function renderShapeDivider(string $position, array $settings): string
    {
        $type = $settings["shape_divider_{$position}"] ?? 'none';
        if ($type === 'none') {
            return '';
        }

        $color = $settings["shape_divider_{$position}_color"] ?? '#ffffff';
        $height = (int) ($settings["shape_divider_{$position}_height"] ?? 100);

        $svgPaths = $this->getShapeDividerPaths();
        if (!isset($svgPaths[$type])) {
            return '';
        }

        $path = $svgPaths[$type];
        $isTop = $position === 'top';
        $style = $isTop
            ? "position:absolute;top:0;left:0;width:100%;height:{$height}px;z-index:1;transform:rotate(180deg);"
            : "position:absolute;bottom:0;left:0;width:100%;height:{$height}px;z-index:1;";
        $preserve = $isTop ? 'none' : 'none';

        return <<<HTML
<div class="pb-shape-divider pb-shape-divider-{$position}" style="{$style}">
    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" style="position:absolute;bottom:0;left:0;width:100%;height:100%;" xmlns="http://www.w3.org/2000/svg">
        <path d="{$path}" style="fill:{$color};"></path>
    </svg>
</div>
HTML;
    }

    protected function getShapeDividerPaths(): array
    {
        return [
            'tilt' => 'M0,0L1200,0L1200,120L0,120Z',
            'waves' => 'M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z',
            'mountains' => 'M0,0L1200,0L1200,120L0,120Z M0,60L200,20L400,80L600,10L800,70L1000,30L1200,60L1200,120L0,120Z',
            'clouds' => 'M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z',
            'triangles' => 'M0,0L1200,0L1200,120L0,120Z M0,0L600,120L1200,0L1200,120L0,120Z',
            'drip' => 'M0,0V120H1200V0C1200,0,1100,60,900,60S500,0,300,60,0,0,0,0Z',
            'clouds-dramatic' => 'M0,0V60c0,0,200-40,400,0s400,60,400,0,200-40,400,0V0Z',
            'tilt-opacity' => 'M0,0L1200,0L1200,120L0,120Z',
            'mountains-peak' => 'M0,0L1200,0L1200,120L0,120Z M0,80L150,30L300,90L500,10L700,70L900,20L1100,60L1200,40L1200,120L0,120Z',
        ];
    }

    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return [
            'r' => (int) hexdec(substr($hex, 0, 2)),
            'g' => (int) hexdec(substr($hex, 2, 2)),
            'b' => (int) hexdec(substr($hex, 4, 2)),
        ];
    }
}
