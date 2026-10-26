<?php

namespace App\Services\PageBuilder\Core;

use App\Models\Page;
use App\Models\Element;
use App\Services\PageBuilder\DynamicTags\DynamicTagService;
use App\Services\PageBuilder\Security\HtmlSanitizer;
use App\Services\PageBuilder\Widgets\ColumnWidget;

class Renderer
{
    protected WidgetManager $widgetManager;
    protected DynamicTagService $dynamicTagService;
    protected string $theme = 'default';

    public function __construct(WidgetManager $widgetManager, ?DynamicTagService $dynamicTagService = null)
    {
        $this->widgetManager = $widgetManager;
        $this->dynamicTagService = $dynamicTagService ?? app(DynamicTagService::class);
    }

    public function render(Page $page, array $options = []): string
    {
        $this->theme = $options['theme'] ?? 'default';

        $this->loadElementTree($page);

        $pageSettings = $page->settings ?? [];

        $containerWidth = $pageSettings['container_width'] ?? $options['container_width'] ?? '1140px';
        $pageBackground = $pageSettings['page_background'] ?? '#ffffff';
        $contentPadding = $pageSettings['content_padding'] ?? '0px';

        $html = $this->renderElements($page->elements);

        $wrapperClass = $options['wrapper_class'] ?? 'page-builder-wrapper';
        $pageStyle = "background-color: {$pageBackground};";
        $innerStyle = "max-width: {$containerWidth}; margin: 0 auto; padding: {$contentPadding};";

        $content = <<<HTML
<div class="{$wrapperClass}" data-page-id="{$page->id}" style="{$pageStyle}">
    <div class="pb-page-inner" style="{$innerStyle}">
        {$html}
    </div>
</div>
HTML;

        if ($options['with_container'] ?? true) {
            $lang = $options['lang'] ?? 'en';
            $hasMath = $this->hasMathElements($page);
            $renderStyles = $this->renderStyles($page, $hasMath);
            $renderScripts = $this->renderScripts($page, $hasMath);
            $title = htmlspecialchars($page->title, ENT_QUOTES, 'UTF-8');
            $csrfToken = csrf_token();
            $csp = implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://www.youtube.com https://player.vimeo.com",
                "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.gstatic.com",
                "img-src 'self' data: blob: https: http:",
                "font-src 'self' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com https://fonts.googleapis.com",
                "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com",
                "connect-src 'self'",
                "media-src 'self' https:",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
            ]);
            $content = <<<HTML
<!DOCTYPE html>
<html lang="{$lang}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{$csrfToken}">
    <meta http-equiv="Content-Security-Policy" content="{$csp}">
    <title>{$title}</title>
    <style>
        body { margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        * { box-sizing: border-box; }
    </style>
    {$renderStyles}
    {$renderScripts}
</head>
<body>
    {$content}
</body>
</html>
HTML;
        }

        return $content;
    }

    public function renderElements($elements, array $options = []): string
    {
        $html = '';

        foreach ($elements as $element) {
            $html .= $this->renderElement($element, $options);
        }

        return $html;
    }

    public function renderElement(Element $element, array $options = []): string
    {
        $widget = $this->widgetManager->getWidget($element->type);

        if (!$widget) {
            return "<!-- Unknown widget type: {$element->type} -->";
        }

        $childrenHtml = '';
        if ($widget->isContainer() && $element->children->isNotEmpty()) {
            $childrenHtml = $this->renderElements($element->children, $options);
        }

        // Children are rendered once; each device variant of this widget gets a placeholder instead
        $placeholder = '<!--pb-children-' . $element->id . '-->';
        $render = fn (array $settings, array $styles) => $widget->render(
            array_merge($settings, $styles),
            array_merge($element->content ?? [], ['children' => $placeholder]),
            $styles
        );

        [$innerHtml, $responsiveCss] = $this->renderResponsiveVariants($element, $widget->isContainer(), $render);
        $innerHtml = str_replace($placeholder, $childrenHtml, $innerHtml);

        return $this->buildElementHtml($element, $innerHtml, $options, false, $responsiveCss);
    }

    /**
     * Renders the desktop version plus tablet/mobile overrides as media-query CSS.
     *
     * @return array{0: string, 1: string} [html, css]
     */
    protected function renderResponsiveVariants(Element $element, bool $isContainer, callable $render): array
    {
        $base = $render($element->settings ?? [], $element->styles ?? []);
        if (!$element->hasDeviceOverrides()) {
            return [$base, ''];
        }

        $variants = [];
        foreach (Element::RESPONSIVE_DEVICES as $device) {
            [$settings, $styles] = $element->valuesForDevice($device);
            $variants[$device] = $render($settings, $styles);
        }
        if (count(array_unique(array_merge([$base], array_values($variants)))) === 1) {
            return [$base, ''];
        }

        $compiled = (new ResponsiveStyleCompiler())->compile($base, $variants, 'e' . $element->id);
        if ($compiled !== null) {
            return [$compiled['html'], $compiled['css']];
        }

        // Markup differs per device (e.g. a different layout option): ship each variant and show
        // the right one. Containers keep desktop markup so their children are never duplicated.
        if ($isContainer) {
            return [$base, ''];
        }

        $id = $element->id;
        $tablet = ResponsiveStyleCompiler::BREAKPOINTS['tablet'];
        $mobile = ResponsiveStyleCompiler::BREAKPOINTS['mobile'];
        $html = '<div class="pb-rv" data-pbrv="' . $id . '-desktop">' . $base . '</div>';
        foreach ($variants as $device => $variant) {
            $html .= '<div class="pb-rv" data-pbrv="' . $id . '-' . $device . '">' . $variant . '</div>';
        }
        $sel = fn (string $device) => '[data-pbrv="' . $id . '-' . $device . '"]';
        $css = '.pb-rv{display:contents}' . $sel('tablet') . ',' . $sel('mobile') . '{display:none}'
            . "@media (max-width: {$tablet}px){" . $sel('desktop') . '{display:none}' . $sel('tablet') . '{display:contents}}'
            . "@media (max-width: {$mobile}px){" . $sel('tablet') . '{display:none}' . $sel('mobile') . '{display:contents}}';

        return [$html, $css];
    }

    /**
     * Inline style of the .pb-element wrapper for a given set of values.
     */
    protected function wrapperStyle(Element $element, array $settings, array $styles): string
    {
        $styleStr = $this->buildStyleString($styles);
        if ($element->type === 'column') {
            $columnWidget = $this->widgetManager->getWidget('column');
            if ($columnWidget instanceof ColumnWidget) {
                $styleStr .= $columnWidget->wrapperStyle(array_merge($settings, $styles));
            }
        }

        return $styleStr;
    }

    /**
     * Media-query rules for the wrapper's own inline style (margins, column width...).
     */
    protected function wrapperResponsiveCss(Element $element, string $baseStyle): string
    {
        if (!$element->hasDeviceOverrides()) {
            return '';
        }
        $compiler = new ResponsiveStyleCompiler();
        $base = $compiler->parseStyle($baseStyle);
        $deviceStyles = [];
        foreach (Element::RESPONSIVE_DEVICES as $device) {
            [$settings, $styles] = $element->valuesForDevice($device);
            $props = $compiler->parseStyle($this->wrapperStyle($element, $settings, $styles));
            if ($props !== $base) {
                $deviceStyles[$device] = $props;
            }
        }
        if ($deviceStyles === []) {
            return '';
        }

        $selector = '.pb-element[data-element-id="' . $element->id . '"]';
        $rules = [];
        foreach ($compiler->deviceRules($base, $deviceStyles, Element::RESPONSIVE_DEVICES) as $device => $props) {
            $rules[$device][] = $selector . '{' . $compiler->declarations($props) . '}';
        }

        return $compiler->wrapMedia($rules);
    }

    public function renderEditor(Page $page): string
    {
        $this->loadElementTree($page);

        $html = '';

        foreach ($page->elements as $element) {
            $html .= $this->renderElementEditor($element);
        }

        return $html;
    }

    public function renderElementEditor(Element $element): string
    {
        $widget = $this->widgetManager->getWidget($element->type);

        if (!$widget) {
            return '<div class="pb-editor-error">Unknown widget</div>';
        }

        $childrenHtml = '';
        if ($widget->isContainer() && $element->children->isNotEmpty()) {
            foreach ($element->children as $child) {
                $childrenHtml .= $this->renderElementEditor($child);
            }
        }

        $mergedSettings = array_merge($element->settings ?? [], $element->styles ?? []);
        $innerHtml = $widget->renderEditor(
            $mergedSettings,
            array_merge($element->content ?? [], ['children' => $childrenHtml]),
            $element->styles ?? []
        );

        $innerHtml = $this->dynamicTagService->processTags($innerHtml, [
            'page' => $element->page ?? null,
            'user' => $element->page?->user ?? null,
        ]);

        $innerHtml = $this->processEmbeds($innerHtml);

        $cssId = $element->css_id ? " id=\"{$element->css_id}\"" : '';
        $classes = "pb-editor-element pb-{$element->type} {$element->column_size} {$this->getCssClasses($element)}";

        $settings = $element->settings ?? [];
        $visDesktop = $settings['visibility_desktop'] ?? true;
        $visTablet = $settings['visibility_tablet'] ?? true;
        $visMobile = $settings['visibility_mobile'] ?? true;
        $visStyle = '';
        if (!$visDesktop || !$visTablet || !$visMobile) {
            $visCss = '';
            if (!$visDesktop) $visCss .= ".pb-editor-element[data-element-id=\"{$element->id}\"]{opacity:.3;}";
            if (!$visTablet) $visCss .= "@media(max-width:1024px){.pb-editor-element[data-element-id=\"{$element->id}\"]{opacity:.3;}}";
            if (!$visMobile) $visCss .= "@media(max-width:767px){.pb-editor-element[data-element-id=\"{$element->id}\"]{opacity:.3;}}";
            $visStyle = "<style>{$visCss}</style>";
        }

        $customCss = $settings['custom_css'] ?? '';
        $customStyle = '';
        if ($customCss) {
            $sanitizedCss = HtmlSanitizer::sanitizeCustomCss($customCss);
            $selector = $cssId ? "#{$element->css_id}" : ".pb-element[data-element-id=\"{$element->id}\"]";
            $customStyle = "<style>{$selector} { {$sanitizedCss} }</style>";
        }

        $visStyle = $visCss ? "<style>{$visCss}</style>" : '';

        return $visStyle . $customStyle . <<<HTML
<div{$cssId} class="{$classes}" data-element-id="{$element->id}" data-element-type="{$element->type}" draggable="true">
    <div class="pb-element-toolbar">
        <span class="pb-element-name">{$element->name}</span>
        <div class="pb-element-actions">
            <button class="pb-btn-duplicate" title="Duplicate">⧉</button>
            <button class="pb-btn-edit" title="Edit">✎</button>
            <button class="pb-btn-delete" title="Delete">✕</button>
        </div>
    </div>
    <div class="pb-element-content">
        {$innerHtml}
    </div>
</div>
HTML;
    }

    protected function buildAttributes(Element $element): string
    {
        $attrs = [];

        if ($element->css_id) {
            $attrs['data-css-id'] = $element->css_id;
        }

        if ($element->animation) {
            $attrs['data-animation'] = json_encode($element->animation);
        }

        if ($element->effects) {
            $attrs['data-effects'] = json_encode($element->effects);
        }

        if ($element->responsive_settings) {
            $attrs['data-responsive'] = json_encode($element->responsive_settings);
        }

        if (empty($attrs)) {
            return '';
        }

        $html = '';
        foreach ($attrs as $key => $value) {
            $html .= " {$key}=\"" . htmlspecialchars($value, ENT_QUOTES) . '"';
        }

        return $html;
    }

    protected function buildStyleString(array $styles): string
    {
        if (empty($styles)) {
            return '';
        }

        $css = '';
        foreach ($styles as $property => $value) {
            // Compound values (gradients, typography groups) are consumed by the widgets themselves
            if (!is_scalar($value) || $value === '') {
                continue;
            }
            $cssProperty = preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $property);
            $cssProperty = str_replace('_', '-', $cssProperty);
            $cssProperty = strtolower($cssProperty);
            $css .= "{$cssProperty}: {$value}; ";
        }

        return $css;
    }

    protected function buildInlineStyles(array $styles): string
    {
        if (empty($styles)) {
            return '';
        }

        $css = $this->buildStyleString($styles);

        return " style=\"{$css}\"";
    }

    protected function getCssClasses(Element $element): string
    {
        $classes = [];

        if ($element->css_classes && is_array($element->css_classes)) {
            $classes = array_merge($classes, $element->css_classes);
        }

        return implode(' ', $classes);
    }

    public function processEmbeds(string $html): string
    {
        $processed = preg_replace_callback(
            '/<iframe\s[^>]*src=["\'](https?:\/\/(?:www\.)?(?:youtube\.com|youtu\.be|youtube-nocookie\.com)[^"\']+)["\'][^>]*>/i',
            function ($matches) {
                $url = $matches[1];
                $videoId = null;

                if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $url, $m)) {
                    $videoId = $m[1];
                } elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $m)) {
                    $videoId = $m[1];
                } elseif (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $url, $m)) {
                    $videoId = $m[1];
                } elseif (preg_match('/youtube-nocookie\.com\/embed\/([a-zA-Z0-9_-]+)/', $url, $m)) {
                    $videoId = $m[1];
                }

                if ($videoId) {
                    // Keep the player options (autoplay, start, mute, controls...) set by the video widget;
                    // a watch?v= URL carries the id in its query, so only the other parameters are kept
                    parse_str(html_entity_decode((string) parse_url($url, PHP_URL_QUERY)), $query);
                    unset($query['v']);
                    $embed = "https://www.youtube.com/embed/{$videoId}" . ($query ? '?' . htmlspecialchars(http_build_query($query), ENT_QUOTES, 'UTF-8') : '');
                    $iframe = str_replace($url, $embed, $matches[0]);
                    if (!str_contains($iframe, 'allow="')) {
                        $iframe = str_replace('<iframe', '<iframe allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen', $iframe);
                    }
                    return $iframe;
                }

                return $matches[0];
            },
            $html
        );

        $processed = preg_replace_callback(
            '/<a\s[^>]*href=["\'](https?:\/\/(?:www\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+))["\'][^>]*>.*?<\/a>/i',
            function ($matches) {
                $videoId = $matches[2];
                return '<iframe width="560" height="315" src="https://www.youtube.com/embed/' . $videoId . '" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';
            },
            $processed
        );

        return $processed;
    }

    public function renderStyles(Page $page, bool $hasMath = false): string
    {
        $hasAnimations = $page->elements
            ->filter(fn($e) => !empty($e->settings['animation']) && $e->settings['animation'] !== 'none')
            ->isNotEmpty();

        $css = "\n<style>\n.pb-drop-cap:first-letter { font-size: 3em; float: left; line-height: 1; margin-right: 10px; }\n";

        $css .= ".pb-section-inner > .pb-element { flex: 0 0 100%; max-width: 100%; }\n";
        $css .= "@media (max-width: 767px) { :where(.pb-section-inner, .pb-inner-section) > :where(.pb-element[data-element-type=\"column\"]) { flex: 0 0 100% !important; max-width: 100% !important; } }\n";
        $css .= ".col-12 { flex: 0 0 100%; max-width: 100%; }\n";
        $css .= ".col-6 { flex: 0 0 50%; max-width: 50%; }\n";
        $css .= ".col-4 { flex: 0 0 33.333%; max-width: 33.333%; }\n";
        $css .= ".col-8 { flex: 0 0 66.666%; max-width: 66.666%; }\n";
        $css .= ".col-3 { flex: 0 0 25%; max-width: 25%; }\n";
        $css .= ".col-md-6 { flex: 0 0 50%; max-width: 50%; }\n";
        $css .= ".col-md-4 { flex: 0 0 33.333%; max-width: 33.333%; }\n";
        $css .= ".col-md-8 { flex: 0 0 66.666%; max-width: 66.666%; }\n";
        $css .= ".col-md-3 { flex: 0 0 25%; max-width: 25%; }\n";

        $css .= ".pb-video-wrapper { position: relative; }\n";
        $css .= ".pb-image-wrapper { line-height: 0; }\n";
        $css .= ".pb-image-wrapper img { max-width: 100%; height: auto; display: block; }\n";
        $css .= ".pb-divider { width: 100%; }\n";
        $css .= ".pb-button { display: inline-block; text-decoration: none; }\n";
        $css .= ".pb-icon-link { display: inline-block; }\n";
        $css .= ".pb-form input, .pb-form textarea, .pb-form select { width: 100%; }\n";
        $css .= ".pb-tabs-wrapper { width: 100%; }\n";
        $css .= ".pb-accordion { width: 100%; }\n";
        $css .= "</style>\n";

        $css .= "\n<link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css\" crossorigin=\"anonymous\" referrerpolicy=\"no-referrer\">\n";

        if ($hasAnimations) {
            $css .= "\n<link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css\">\n";
            $css .= "\n<style>.pb-animate { animation-fill-mode: both; }\n.pb-animate-slow { animation-duration: 1s; }\n.pb-animate-fast { animation-duration: 0.5s; }\n</style>\n";
        }

        if ($page->settings['custom_css'] ?? false) {
            $sanitizedCss = HtmlSanitizer::sanitizeCustomCss($page->settings['custom_css']);
            $css .= "\n<style>\n{$sanitizedCss}\n</style>\n";
        }

        if ($page->settings['google_fonts'] ?? false) {
            $fonts = $page->settings['google_fonts'];
            foreach ($fonts as $font) {
                $css .= "\n<link href=\"https://fonts.googleapis.com/css2?family={$font}:wght@300;400;500;600;700&display=swap\" rel=\"stylesheet\">\n";
            }
        }

        if ($hasMath) {
            $css .= "\n<link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css\">\n";
        }

        $css .= "\n<style>.pb-popup-content { animation: popupFadeIn .3s ease; }\n@keyframes popupFadeIn { from { opacity: 0; transform: scale(.95) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }\n.pb-popup-overlay { transition: opacity .2s; }\n</style>\n";

        return $css;
    }

    public function renderScripts(Page $page, bool $hasMath = false): string
    {
        $scripts = '';

        if ($page->settings['custom_js'] ?? false) {
            $scripts .= "\n<script>\n" . HtmlSanitizer::sanitizeCustomJs($page->settings['custom_js']) . "\n</script>\n";
        }

        if ($page->settings['custom_js_footer'] ?? false) {
            $scripts .= "\n<script>\n" . HtmlSanitizer::sanitizeCustomJs($page->settings['custom_js_footer']) . "\n</script>\n";
        }

        if ($hasMath) {
            $scripts .= "\n<script src=\"https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js\"></script>\n";
            $scripts .= "\n<script>\ndocument.addEventListener('DOMContentLoaded',function(){document.querySelectorAll('.pb-math').forEach(function(el){try{katex.render(el.getAttribute('data-formula'),el,{displayMode:el.getAttribute('data-display')==='true',throwOnError:false})}catch(e){el.textContent=el.getAttribute('data-formula')}})})\n</script>\n";
        }

        $scripts .= "\n<script>\nclass PopupManager{constructor(){this.openPopups=[];this.init()}init(){document.querySelectorAll('.pb-popup-overlay').forEach(p=>{this.setupPopup(p)})}setupPopup(p){const t=JSON.parse(p.dataset.triggers||'[]'),c=p.querySelector('.pb-popup-close');c&&(c.onclick=()=>this.close(p));p.onclick=e=>{e.target===p&&this.close(p)};t.forEach(r=>{switch(r.type){case 'on_load':document.addEventListener('DOMContentLoaded',()=>this.open(p));break;case 'on_timer':setTimeout(()=>this.open(p),(r.value||3)*1e3);break;case 'on_scroll':window.addEventListener('scroll',()=>{if(!p._triggered){const s=(window.scrollY/(document.documentElement.scrollHeight-window.innerHeight))*100;s>=(parseFloat(r.value)||50)&&(p._triggered=!0,this.open(p))}});break;case 'on_exit':document.addEventListener('mouseleave',e=>{e.clientY<=0&&!p._triggered&&(p._triggered=!0,this.open(p))});break;case 'on_click':r.value&&document.querySelectorAll(r.value).forEach(el=>{el.addEventListener('click',()=>this.open(p))})}})}open(p){this.openPopups.length>0||(p.style.display='flex',this.openPopups.push(p),document.body.style.overflow='hidden')}close(p){p.style.display='none',this.openPopups=this.openPopups.filter(o=>o!==p),0===this.openPopups.length&&(document.body.style.overflow='')}}document.addEventListener('DOMContentLoaded',()=>{window._popupManager=new PopupManager()});\n</script>\n";

        return $scripts;
    }

    public function renderSingleElement(Element $element, string $device = 'desktop'): string
    {
        $widget = $this->widgetManager->getWidget($element->type);

        if (!$widget) {
            return "<!-- Unknown widget type: {$element->type} -->";
        }

        $childrenHtml = '';
        if ($element->children->isNotEmpty()) {
            foreach ($element->children as $child) {
                $childrenHtml .= $this->renderSingleElement($child, $device);
            }
        }

        [$settings, $styles] = $element->valuesForDevice($device);
        $innerHtml = $widget->render(
            array_merge($settings, $styles),
            array_merge($element->content ?? [], ['children' => $childrenHtml]),
            $styles
        );

        return $this->buildElementHtml($element, $innerHtml, [], true);
    }

    private function buildElementHtml(Element $element, string $innerHtml, array $options = [], bool $isSingle = false, string $responsiveCss = ''): string
    {
        $innerHtml = $this->dynamicTagService->processTags($innerHtml, [
            'page' => $element->page ?? null,
            'user' => $element->page?->user ?? null,
        ]);

        $innerHtml = $this->processEmbeds($innerHtml);

        $attributes = $this->buildAttributes($element);
        $styleStr = $this->wrapperStyle($element, $element->settings ?? [], $element->styles ?? []);
        $responsiveCss .= $isSingle ? '' : $this->wrapperResponsiveCss($element, $styleStr);
        $styleAttr = $styleStr ? " style=\"{$styleStr}\"" : '';
        $cssId = $element->css_id ? " id=\"{$element->css_id}\"" : '';
        $cssClasses = "pb-element pb-{$element->type} {$element->column_size} {$this->getCssClasses($element)}";

        $settings = $element->settings ?? [];

        if (!empty($settings['animation']) && $settings['animation'] !== 'none') {
            $animClass = 'pb-animate ' . htmlspecialchars($settings['animation'], ENT_QUOTES);
            $duration = $settings['animation_duration'] ?? 'normal';
            if ($duration === 'slow') $animClass .= ' pb-animate-slow';
            elseif ($duration === 'fast') $animClass .= ' pb-animate-fast';
            $delay = (int) ($settings['animation_delay'] ?? 0);
            if ($delay > 0) $styleStr .= " animation-delay: {$delay}ms;";
            $styleAttr = $styleStr ? " style=\"{$styleStr}\"" : '';
            $cssClasses .= ' ' . $animClass;
        }

        $visDesktop = $settings['visibility_desktop'] ?? true;
        $visTablet = $settings['visibility_tablet'] ?? true;
        $visMobile = $settings['visibility_mobile'] ?? true;
        $visCss = '';
        if (!$visDesktop) $visCss .= ".pb-element[data-element-id=\"{$element->id}\"]{display:none !important;}";
        if (!$visTablet) $visCss .= "@media(max-width:1024px){.pb-element[data-element-id=\"{$element->id}\"]{display:none !important;}}";
        if (!$visMobile) $visCss .= "@media(max-width:767px){.pb-element[data-element-id=\"{$element->id}\"]{display:none !important;}}";

        $customCss = $settings['custom_css'] ?? '';
        $customStyle = '';
        if ($customCss) {
            $sanitizedCss = HtmlSanitizer::sanitizeCustomCss($customCss);
            $selector = $cssId ? "#{$element->css_id}" : ".pb-element[data-element-id=\"{$element->id}\"]";
            $customStyle = "<style>{$selector} { {$sanitizedCss} }</style>";
        }

        $visStyle = $visCss ? "<style>{$visCss}</style>" : '';
        if ($responsiveCss !== '') {
            $visStyle .= '<style data-pb-responsive="' . $element->id . '">' . $responsiveCss . '</style>';
        }

        if ($isSingle) {
            return $visStyle . $customStyle . <<<HTML
<div{$cssId} class="{$cssClasses}" data-element-id="{$element->id}" data-element-type="{$element->type}"{$attributes}{$styleAttr}>
    <div class="pb-element-toolbar">
        <span class="pb-el-name">{$element->name}</span>
        <span class="pb-el-type">{$element->type}</span>
    </div>
    <div class="pb-el-content">{$innerHtml}</div>
</div>
HTML;
        }

        return $visStyle . $customStyle . <<<HTML
<div{$cssId} class="{$cssClasses}" data-element-id="{$element->id}" data-element-type="{$element->type}"{$attributes}{$styleAttr}>
    {$innerHtml}
</div>
HTML;
    }

    protected function loadElementTree(Page $page): void
    {
        $allElements = $page->allElements()->get()->keyBy('id');

        $rootElements = $allElements->whereNull('parent_id')->sortBy('order')->values();

        foreach ($allElements as $element) {
            $children = $allElements->where('parent_id', $element->id)->sortBy('order')->values();
            $element->setRelation('children', $children);
        }

        $page->setRelation('elements', $rootElements);
    }

    public function hasMathElements(Page $page): bool
    {
        $all = collect();
        $flatten = function ($elements) use (&$all, &$flatten) {
            foreach ($elements as $e) {
                $all->push($e);
                if ($e->children->isNotEmpty()) {
                    $flatten($e->children);
                }
            }
        };
        $flatten($page->elements);

        return $all->contains(fn($e) => $e->type === 'math')
            || $all->filter(fn($e) => $e->type === 'text')
                ->contains(fn($e) => str_contains($e->settings['content'] ?? '', 'pb-math'));
    }

    public function getWidgetControls(string $type): ?array
    {
        $widget = $this->widgetManager->getWidget($type);
        if (!$widget) return null;

        return [
            'type' => $widget->getType(),
            'label' => $widget->getLabel(),
            'icon' => $widget->getIcon(),
            'categories' => $widget->getCategories(),
            'is_container' => $widget->isContainer(),
            'default_settings' => $widget->getDefaultSettings(),
            'controls' => ControlTranslator::controls($widget->getControls()),
        ];
    }

    public function renderJson(Page $page): array
    {
        $this->loadElementTree($page);

        return [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'settings' => $page->settings,
            'meta_data' => $page->meta_data,
            'elements' => $this->renderElementsJson($page->elements),
        ];
    }

    protected function renderElementsJson($elements): array
    {
        $result = [];

        foreach ($elements as $element) {
            $data = [
                'id' => $element->uuid,
                'type' => $element->type,
                'name' => $element->name,
                'settings' => $element->settings,
                'content' => $element->content,
                'styles' => $element->styles,
                'responsive_settings' => $element->responsive_settings,
                'animation' => $element->animation,
                'effects' => $element->effects,
                'column_size' => $element->column_size,
            ];

            if ($element->children->isNotEmpty()) {
                $data['children'] = $this->renderElementsJson($element->children);
            }

            $result[] = $data;
        }

        return $result;
    }
}
