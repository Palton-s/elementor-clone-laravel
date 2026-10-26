<?php

namespace App\Services\PageBuilder\Security;

class HtmlSanitizer
{
    public static function sanitize(string $html, array $options = []): string
    {
        $html = self::removeEventHandlers($html);
        $html = self::sanitizeUrls($html);

        if ($options['strip_scripts'] ?? true) {
            $html = self::removeScriptTags($html);
        }

        return $html;
    }

    public static function sanitizeCustomCss(?string $css): string
    {
        if ($css === null || $css === '') {
            return '';
        }

        $css = preg_replace('/expression\s*\(/i', '', $css);
        $css = preg_replace('/javascript\s*:/i', '', $css);
        $css = preg_replace('/vbscript\s*:/i', '', $css);
        $css = preg_replace('/-moz-binding\s*:/i', '', $css);

        return strip_tags($css);
    }

    public static function sanitizeCustomJs(?string $js): string
    {
        if ($js === null || $js === '') {
            return '';
        }

        $js = preg_replace('/document\.write\s*\(/i', '// ', $js);
        $js = preg_replace('/eval\s*\(/i', '// ', $js);

        return $js;
    }

    private static ?\HTMLPurifier $purifier = null;

    /**
     * Rich text typed in the editor (text blocks, descriptions): an allowlist of formatting tags,
     * links, images, tables, video and YouTube/Vimeo/OpenStreetMap iframes. Everything else,
     * including event handlers and javascript: URLs, is removed.
     */
    public static function sanitizeRichText(string $html): string
    {
        if (trim($html) === '') {
            return $html;
        }

        return self::purifier()->purify($html);
    }

    private static function purifier(): \HTMLPurifier
    {
        if (self::$purifier !== null) {
            return self::$purifier;
        }

        $config = \HTMLPurifier_Config::createDefault();
        $cacheDir = storage_path('framework/cache/htmlpurifier');
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }
        $config->set('Cache.SerializerPath', $cacheDir);
        $config->set('HTML.DefinitionID', 'page-builder-rich-text');
        $config->set('HTML.DefinitionRev', 1);
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('HTML.Allowed', implode(',', [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'sup', 'sub', 'mark', 'small', 'span', 'div',
            'ul', 'ol', 'li', 'dl', 'dt', 'dd', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'code', 'hr',
            'a[href|title|target|rel]', 'img[src|alt|title|width|height]',
            'table', 'thead', 'tbody', 'tfoot', 'tr', 'th[colspan|rowspan]', 'td[colspan|rowspan]', 'caption',
            'figure', 'figcaption', 'video[src|controls|width|height|poster|loop|muted|preload]', 'source[src|type]',
            'iframe[src|width|height|frameborder|allowfullscreen|title]',
            '*[class|style|id|title]',
        ]));
        $config->set('Attr.AllowedFrameTargets', ['_blank', '_self']);
        $config->set('Attr.EnableID', true);
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(-nocookie)?\.com/embed/|player\.vimeo\.com/video/|www\.openstreetmap\.org/export/embed\.html)%');
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
        $config->set('AutoFormat.RemoveEmpty', false);

        // HTML5 elements HTMLPurifier does not know out of the box
        if ($def = $config->maybeGetRawHTMLDefinition()) {
            $def->addElement('figure', 'Block', 'Flow', 'Common');
            $def->addElement('figcaption', 'Block', 'Flow', 'Common');
            $def->addElement('mark', 'Inline', 'Inline', 'Common');
            $def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', [
                'src' => 'URI', 'controls' => 'Bool', 'loop' => 'Bool', 'muted' => 'Bool',
                'width' => 'Length', 'height' => 'Length', 'poster' => 'URI', 'preload' => 'Enum#auto,metadata,none',
            ]);
            $def->addElement('source', 'Block', 'Empty', 'Common', ['src' => 'URI', 'type' => 'Text']);
            $def->addAttribute('iframe', 'allowfullscreen', 'Bool');
        }

        return self::$purifier = new \HTMLPurifier($config);
    }

    /**
     * A value that ends up inside a style="" attribute: no tag delimiters, and double quotes become
     * single quotes so CSS like font-family "Open Sans" keeps working without closing the attribute.
     */
    public static function sanitizeCssValue(string $value): string
    {
        return str_replace(['"', '<', '>'], ["'", '', ''], $value);
    }

    /**
     * A value used as href/src: dangerous schemes are dropped and characters that could close
     * the attribute are percent-encoded.
     */
    public static function sanitizeUrlValue(string $url): string
    {
        $url = trim($url);
        if (self::isDangerousUrl($url)) {
            return '';
        }

        return str_replace(['"', "'", '<', '>', '`', ' '], ['%22', '%27', '%3C', '%3E', '%60', '%20'], $url);
    }

    /** CSS class list / id: only characters that are valid in selectors. */
    public static function sanitizeIdentifiers(string $value): string
    {
        return trim(preg_replace('/[^A-Za-z0-9_\-: ]+/', '', $value));
    }

    private static function removeEventHandlers(string $html): string
    {
        // Only inside tags, so plain text such as "onboarding = easy" is left alone;
        // "/" also separates attributes in HTML (<img/onerror=...>)
        return preg_replace_callback('/<[a-zA-Z][^>]*>/s', function ($tag) {
            $clean = preg_replace('/[\s\/]+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/si', ' ', $tag[0]);
            return preg_replace('/\s+>$/', '>', $clean);
        }, $html);
    }

    private static function removeScriptTags(string $html): string
    {
        return preg_replace('/<script\b[^>]*>(.*?)<\/script>/si', '', $html);
    }

    private static function sanitizeUrls(string $html): string
    {
        $html = preg_replace('/\s+srcdoc\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/si', '', $html);

        return preg_replace_callback(
            '/((?:href|src|action|formaction|poster)\s*=\s*)("[^"]*"|\'[^\']*\'|[^\s>]+)/si',
            function ($matches) {
                $value = trim($matches[2], " \t\n\r\0\x0B\"'");

                if (self::isDangerousUrl($value)) {
                    return $matches[1] . '"#"';
                }

                return $matches[0];
            },
            $html
        );
    }

    public static function isDangerousUrl(string $url): bool
    {
        $url = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $url = preg_replace('/[\x00-\x20]+/', '', $url);

        return (bool) preg_match(
            '/^(javascript|vbscript|data:text\/html|data:application\/xhtml\+xml|data:image\/svg\+xml)/i',
            $url
        );
    }
}
