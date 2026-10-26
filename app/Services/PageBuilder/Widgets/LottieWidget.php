<?php

namespace App\Services\PageBuilder\Widgets;

class LottieWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'lottie';
        $this->label = 'Lottie Animation';
        $this->icon = 'fas fa-film';
        $this->categories = ['pro'];
        $this->keywords = ['lottie', 'animation', 'motion', 'json', 'vector'];

        $this->defaultSettings = [
            'lottie_url' => '',
            'loop' => true,
            'autoplay' => true,
            'speed' => 1,
            'direction' => 1,
            'background' => '',
            'width' => 300,
            'height' => 300,
            'alignment' => 'center',
            'on_click_action' => 'none',
            'renderer' => 'svg',
        ];

        $this->controls = [
            'lottie_url' => ['type' => 'url', 'label' => 'Lottie JSON URL', 'required' => true],
            'loop' => ['type' => 'boolean', 'label' => 'Loop', 'default' => true],
            'autoplay' => ['type' => 'boolean', 'label' => 'Autoplay', 'default' => true],
            'speed' => ['type' => 'number', 'label' => 'Speed', 'default' => 1, 'min' => 0.1, 'max' => 10, 'step' => 0.1],
            'direction' => ['type' => 'select', 'label' => 'Direction', 'options' => [1 => 'Normal', -1 => 'Reverse']],
            'background' => ['type' => 'color', 'label' => 'Background', 'tab' => 'style'],
            'width' => ['type' => 'number', 'label' => 'Width (px)', 'default' => 300, 'min' => 50, 'max' => 2000, 'tab' => 'style'],
            'height' => ['type' => 'number', 'label' => 'Height (px)', 'default' => 300, 'min' => 50, 'max' => 2000, 'tab' => 'style'],
            'alignment' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left', 'center', 'right'], 'tab' => 'style'],
            'on_click_action' => ['type' => 'select', 'label' => 'On Click Action', 'options' => ['none', 'play', 'pause', 'stop', 'loop'], 'tab' => 'advanced'],
            'renderer' => ['type' => 'select', 'label' => 'Renderer', 'options' => ['svg', 'canvas', 'html'], 'tab' => 'advanced'],
        ];
    }

    public function render(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $url = htmlspecialchars($settings['lottie_url'], ENT_QUOTES, 'UTF-8');
        $loop = $settings['loop'] ? 'loop' : '';
        $autoplay = $settings['autoplay'] ? 'autoplay' : '';
        $speed = (float) $settings['speed'];
        $direction = (int) $settings['direction'];
        $background = htmlspecialchars($settings['background'], ENT_QUOTES, 'UTF-8');
        $width = (int) $settings['width'];
        $height = (int) $settings['height'];
        $alignment = $settings['alignment'];
        $onClick = $settings['on_click_action'];
        $renderer = htmlspecialchars($settings['renderer'], ENT_QUOTES, 'UTF-8');

        $alignMap = ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'];
        $alignVal = $alignMap[$alignment] ?? 'center';

        $playerStyle = "width: {$width}px; height: {$height}px;";
        if ($background) {
            $playerStyle .= " background: {$background};";
        }

        $lottieAttr = '';
        if ($url) {
            $lottieAttr .= " src=\"{$url}\"";
        }
        if ($loop) {
            $lottieAttr .= " loop";
        }
        if ($autoplay) {
            $lottieAttr .= " autoplay";
        }
        if ($speed !== 1.0) {
            $lottieAttr .= " speed=\"{$speed}\"";
        }
        if ($direction !== 1) {
            $lottieAttr .= " direction=\"{$direction}\"";
        }
        if ($background) {
            $lottieAttr .= " background=\"{$background}\"";
        }
        if ($renderer !== 'svg') {
            $lottieAttr .= " renderer=\"{$renderer}\"";
        }

        $onClickAttr = '';
        if ($onClick !== 'none') {
            $onClickAttr = " onclick=\"this.{$onClick}()\"";
        }

        $playerHtml = $url
            ? "<lottie-player{$lottieAttr} style=\"{$playerStyle}\"{$onClickAttr}></lottie-player>"
            : '';

        $cdnScript = '<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js" defer></script>';

        return $cdnScript . "<div class=\"pb-lottie\" style=\"display: flex; justify-content: {$alignVal};\">{$playerHtml}</div>";
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $url = htmlspecialchars($settings['lottie_url'], ENT_QUOTES, 'UTF-8');
        $loop = $settings['loop'] ? 'loop' : '';
        $autoplay = $settings['autoplay'] ? 'autoplay' : '';
        $speed = (float) $settings['speed'];
        $direction = (int) $settings['direction'];
        $background = htmlspecialchars($settings['background'], ENT_QUOTES, 'UTF-8');
        $width = (int) $settings['width'];
        $height = (int) $settings['height'];
        $alignment = $settings['alignment'];
        $onClick = $settings['on_click_action'];
        $renderer = htmlspecialchars($settings['renderer'], ENT_QUOTES, 'UTF-8');

        $alignMap = ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'];
        $alignVal = $alignMap[$alignment] ?? 'center';

        if (!$url) {
            return '<div class="pb-lottie-editor" style="display: flex; justify-content: ' . $alignVal . '; padding: 20px; border: 2px dashed #d1d5db; border-radius: 8px; background: #f9fafb;"><div style="text-align: center; color: #9ca3af;"><div style="font-size: 2rem; margin-bottom: 8px;">🎬</div><div style="font-size: 0.85rem;">Animação Lottie<br><span style="font-size: 0.75rem;">Configure a URL do arquivo .json</span></div></div></div>';
        }

        $playerStyle = "width: {$width}px; height: {$height}px;";
        if ($background) {
            $playerStyle .= " background: {$background};";
        }

        $lottieAttr = '';
        if ($url) {
            $lottieAttr .= " src=\"{$url}\"";
        }
        if ($loop) {
            $lottieAttr .= " loop";
        }
        if ($autoplay) {
            $lottieAttr .= " autoplay";
        }
        if ($speed !== 1.0) {
            $lottieAttr .= " speed=\"{$speed}\"";
        }
        if ($direction !== 1) {
            $lottieAttr .= " direction=\"{$direction}\"";
        }
        if ($background) {
            $lottieAttr .= " background=\"{$background}\"";
        }
        if ($renderer !== 'svg') {
            $lottieAttr .= " renderer=\"{$renderer}\"";
        }

        $onClickAttr = '';
        if ($onClick !== 'none') {
            $onClickAttr = " onclick=\"this.{$onClick}()\"";
        }

        $playerHtml = "<lottie-player{$lottieAttr} style=\"{$playerStyle}\"{$onClickAttr}></lottie-player>";

        return "<div class=\"pb-lottie-editor\" style=\"display: flex; justify-content: {$alignVal};\">{$playerHtml}</div>";
    }
}
