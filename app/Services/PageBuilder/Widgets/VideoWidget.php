<?php

namespace App\Services\PageBuilder\Widgets;

class VideoWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'video';
        $this->label = 'Video';
        $this->icon = '🎬';
        $this->categories = ['basic', 'media'];
        $this->keywords = ['video', 'youtube', 'vimeo', 'embed', 'media'];

        $this->defaultSettings = [
            'video_type' => 'youtube',
            'video_url' => '',
            'video_file' => '',
            'autoplay' => false,
            'loop' => false,
            'controls' => true,
            'mute' => false,
            'start_time' => 0,
            'end_time' => 0,
            'aspect_ratio' => '16:9',
            'alignment' => 'center',
            'width' => '100%',
            'max_width' => '100%',
        ];

        $this->controls = [
            'video_type' => ['type' => 'select', 'label' => 'Video Type', 'options' => ['youtube', 'vimeo', 'custom', 'upload']],
            'video_url' => ['type' => 'url', 'label' => 'Video URL', 'required' => true],
            'video_file' => ['type' => 'video', 'label' => 'Upload Video'],
            'aspect_ratio' => ['type' => 'select', 'label' => 'Aspect Ratio', 'options' => ['16:9', '4:3', '1:1', '21:9']],
            'autoplay' => ['type' => 'boolean', 'label' => 'Autoplay'],
            'loop' => ['type' => 'boolean', 'label' => 'Loop'],
            'controls' => ['type' => 'boolean', 'label' => 'Show Controls'],
            'mute' => ['type' => 'boolean', 'label' => 'Mute'],
            'start_time' => ['type' => 'number', 'label' => 'Start Time (seconds)'],
            'end_time' => ['type' => 'number', 'label' => 'End Time (seconds)'],
            'alignment' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left', 'center', 'right'], 'tab' => 'style'],
            'width' => ['type' => 'text', 'label' => 'Width', 'tab' => 'style'],
            'max_width' => ['type' => 'text', 'label' => 'Max Width', 'tab' => 'style'],
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
        $videoUrl = $settings['video_url'];
        $videoType = $settings['video_type'];
        $videoFile = $settings['video_file'] ?? '';
        $autoplay = $settings['autoplay'];
        $loop = $settings['loop'];
        $controls = $settings['controls'];
        $mute = $settings['mute'];
        $startTime = (int) $settings['start_time'];
        $endTime = (int) $settings['end_time'];
        $aspectRatio = $settings['aspect_ratio'];
        $alignment = $settings['alignment'];
        $width = $settings['width'];
        $maxWidth = $settings['max_width'];

        $fileSource = $this->fileSource($videoType, $videoUrl, $videoFile);
        if ($fileSource !== null) {
            return $this->renderLocalVideo($fileSource, $autoplay, $loop, $controls, $mute, $aspectRatio, $alignment, $width, $maxWidth);
        }

        if (empty($videoUrl)) {
            return '<div class="pb-video-placeholder" style="text-align:center;padding:2rem;color:#999;background:#f5f5f5;border-radius:8px;">Nenhum vídeo selecionado</div>';
        }

        $embedUrl = $this->getEmbedUrl($videoUrl, $videoType, $autoplay, $loop, $controls, $mute, $startTime, $endTime);

        $ratioMap = [
            '16:9' => '56.25%',
            '4:3' => '75%',
            '1:1' => '100%',
            '21:9' => '42.86%',
        ];
        $paddingBottom = $ratioMap[$aspectRatio] ?? '56.25%';

        $wrapperStyle = "width: {$width}; max-width: {$maxWidth}; margin: 0;";
        if ($alignment === 'center') {
            $wrapperStyle .= ' margin-left: auto; margin-right: auto;';
        } elseif ($alignment === 'right') {
            $wrapperStyle .= ' margin-left: auto;';
        }

        $iframeStyle = "position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;";

        $sanitizedUrl = htmlspecialchars($embedUrl, ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars($videoType . ' video', ENT_QUOTES, 'UTF-8');

        return <<<HTML
<div class="pb-video-wrapper" style="{$wrapperStyle}">
    <div class="pb-video-container" style="position: relative; padding-bottom: {$paddingBottom}; height: 0; overflow: hidden; border-radius: 8px;">
        <iframe src="{$sanitizedUrl}" style="{$iframeStyle}" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" title="{$title}"></iframe>
    </div>
</div>
HTML;
    }

    /**
     * URL to play with a <video> tag, or null for an embed (YouTube/Vimeo).
     * An uploaded file wins when the type is "upload" or no embed URL was given (uploads made
     * before the type switched automatically kept "youtube"); a direct .mp4/.webm URL also plays natively.
     */
    private function fileSource($videoType, $videoUrl, $videoFile): ?string
    {
        [$videoType, $videoUrl, $videoFile] = [(string) $videoType, trim((string) $videoUrl), trim((string) $videoFile)];
        if ($videoFile !== '' && ($videoType === 'upload' || $videoUrl === '')) {
            return $videoFile;
        }
        if ($videoType !== 'upload' && $videoUrl !== '' && preg_match('/\.(mp4|webm|ogv|ogg|mov|m4v)(\?.*)?$/i', $videoUrl)) {
            return $videoUrl;
        }

        return null;
    }

    private function renderLocalVideo(string $fileUrl, bool $autoplay, bool $loop, bool $controls, bool $mute, string $aspectRatio, string $alignment, string $width, string $maxWidth): string
    {
        $ratioMap = [
            '16:9' => '56.25%',
            '4:3' => '75%',
            '1:1' => '100%',
            '21:9' => '42.86%',
        ];
        $paddingBottom = $ratioMap[$aspectRatio] ?? '56.25%';

        $wrapperStyle = "width: {$width}; max-width: {$maxWidth}; margin: 0;";
        if ($alignment === 'center') {
            $wrapperStyle .= ' margin-left: auto; margin-right: auto;';
        } elseif ($alignment === 'right') {
            $wrapperStyle .= ' margin-left: auto;';
        }

        $sanitizedUrl = htmlspecialchars($fileUrl, ENT_QUOTES, 'UTF-8');
        $autoplayAttr = $autoplay ? ' autoplay' : '';
        $loopAttr = $loop ? ' loop' : '';
        $controlsAttr = $controls ? ' controls' : '';
        $muteAttr = $mute ? ' muted' : '';

        return <<<HTML
<div class="pb-video-wrapper" style="{$wrapperStyle}">
    <div class="pb-video-container" style="position: relative; padding-bottom: {$paddingBottom}; height: 0; overflow: hidden; border-radius: 8px;">
        <video src="{$sanitizedUrl}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; border: 0;"{$autoplayAttr}{$loopAttr}{$controlsAttr}{$muteAttr} playsinline></video>
    </div>
</div>
HTML;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $videoUrl = $settings['video_url'];
        $videoType = $settings['video_type'];
        $videoFile = $settings['video_file'] ?? '';
        $aspectRatio = $settings['aspect_ratio'];
        $alignment = $settings['alignment'];
        $width = $settings['width'];
        $maxWidth = $settings['max_width'];

        $fileSource = $this->fileSource($videoType, $videoUrl, $videoFile);
        if ($fileSource !== null) {
            return $this->renderLocalVideo($fileSource, false, false, true, false, $aspectRatio, $alignment, $width, $maxWidth);
        }

        if (empty($videoUrl)) {
            return '<div class="pb-video-placeholder" style="text-align:center;padding:2rem;color:#999;background:#f5f5f5;border-radius:8px;cursor:pointer;">🎬 Clique para adicionar um vídeo</div>';
        }

        $embedUrl = $this->getEmbedUrl($videoUrl, $videoType, false, false, true, false, 0, 0);

        $ratioMap = [
            '16:9' => '56.25%',
            '4:3' => '75%',
            '1:1' => '100%',
            '21:9' => '42.86%',
        ];
        $paddingBottom = $ratioMap[$aspectRatio] ?? '56.25%';

        $wrapperStyle = "width: {$width}; max-width: {$maxWidth}; margin: 0;";
        if ($alignment === 'center') {
            $wrapperStyle .= ' margin-left: auto; margin-right: auto;';
        } elseif ($alignment === 'right') {
            $wrapperStyle .= ' margin-left: auto;';
        }

        $iframeStyle = "position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;";
        $sanitizedUrl = htmlspecialchars($embedUrl, ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars($videoType . ' video', ENT_QUOTES, 'UTF-8');

        return <<<HTML
<div class="pb-video-wrapper" style="{$wrapperStyle}">
    <div class="pb-video-container" style="position: relative; padding-bottom: {$paddingBottom}; height: 0; overflow: hidden; border-radius: 8px;">
        <iframe src="{$sanitizedUrl}" style="{$iframeStyle}" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" title="{$title}"></iframe>
    </div>
</div>
HTML;
    }

    private function getEmbedUrl(string $url, string $type, bool $autoplay, bool $loop, bool $controls, bool $mute, int $startTime, int $endTime): string
    {
        $url = trim($url);

        return match ($type) {
            'youtube' => $this->getYoutubeEmbedUrl($url, $autoplay, $loop, $controls, $mute, $startTime, $endTime),
            'vimeo' => $this->getVimeoEmbedUrl($url, $autoplay, $loop, $mute),
            'upload' => $url,
            default => $url,
        };
    }

    private function getYoutubeEmbedUrl(string $url, bool $autoplay, bool $loop, bool $controls, bool $mute, int $startTime, int $endTime): string
    {
        $videoId = $this->extractYoutubeId($url);

        if (!$videoId) {
            return $url;
        }

        $params = [];
        $params[] = 'rel=0';
        $params[] = $controls ? 'controls=1' : 'controls=0';
        $params[] = $autoplay ? 'autoplay=1' : 'autoplay=0';
        $params[] = $loop ? 'loop=1' : 'loop=0';
        $params[] = $mute ? 'mute=1' : 'mute=0';

        if ($loop && $videoId) {
            $params[] = "playlist={$videoId}";
        }

        if ($startTime > 0) {
            $params[] = "start={$startTime}";
        }

        if ($endTime > 0) {
            $params[] = "end={$endTime}";
        }

        $queryString = implode('&', $params);

        return "https://www.youtube.com/embed/{$videoId}?{$queryString}";
    }

    private function extractYoutubeId(string $url): ?string
    {
        $patterns = [
            '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/v\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    private function getVimeoEmbedUrl(string $url, bool $autoplay, bool $loop, bool $mute): string
    {
        $videoId = $this->extractVimeoId($url);

        if (!$videoId) {
            return $url;
        }

        $params = [];
        $params[] = $autoplay ? 'autoplay=1' : 'autoplay=0';
        $params[] = $loop ? 'loop=1' : 'loop=0';
        $params[] = $mute ? 'muted=1' : 'muted=0';
        $params[] = 'title=0';
        $params[] = 'byline=0';
        $params[] = 'portrait=0';

        $queryString = implode('&', $params);

        return "https://player.vimeo.com/video/{$videoId}?{$queryString}";
    }

    private function extractVimeoId(string $url): ?string
    {
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
