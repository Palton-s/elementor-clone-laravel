<?php

namespace App\Services\PageBuilder\Core;

/**
 * Turns per-device widget renders into media-query CSS.
 *
 * Widgets write their styles inline, so a tablet/mobile render is compared tag by tag
 * with the desktop render: when only style attributes differ, the changed tags get a
 * data-pbr marker and @media rules carry the changed properties (!important, since
 * they must beat the inline style). When the markup itself differs, the caller falls
 * back to rendering each variant and showing the right one per breakpoint.
 */
class ResponsiveStyleCompiler
{
    public const BREAKPOINTS = ['tablet' => 1024, 'mobile' => 767];

    /**
     * @param string $base desktop html
     * @param array<string, string> $variants device => html (cascaded values)
     * @return array{html: string, css: string}|null null when the markup is not comparable
     */
    public function compile(string $base, array $variants, string $markerPrefix): ?array
    {
        $baseTokens = $this->tokenize($base);
        $tagDiffs = [];

        foreach ($variants as $device => $html) {
            $tokens = $this->tokenize($html);
            if (count($tokens) !== count($baseTokens)) {
                return null;
            }
            foreach ($baseTokens as $i => $token) {
                $other = $tokens[$i];
                if (!$this->isTag($token)) {
                    if (trim($token) !== trim($other)) {
                        return null;
                    }
                    continue;
                }
                $a = $this->parseTag($token);
                $b = $this->parseTag($other);
                if ($a === null || $b === null || $a['name'] !== $b['name'] || $a['attrs'] !== $b['attrs']) {
                    if ($token !== $other) {
                        return null;
                    }
                    continue;
                }
                if ($a['style'] !== $b['style']) {
                    $tagDiffs[$i][$device] = [$this->parseStyle($a['style']), $this->parseStyle($b['style'])];
                }
            }
        }

        if ($tagDiffs === []) {
            return ['html' => $base, 'css' => ''];
        }

        $css = [];
        foreach ($tagDiffs as $i => $byDevice) {
            $selector = '[data-pbr="' . $markerPrefix . '-' . $i . '"]';
            $baseStyle = reset($byDevice)[0];
            foreach ($this->deviceRules($baseStyle, array_map(fn ($pair) => $pair[1], $byDevice), array_keys($variants)) as $device => $props) {
                $css[$device][] = $selector . '{' . $this->declarations($props) . '}';
            }
            $baseTokens[$i] = $this->addMarker($baseTokens[$i], $markerPrefix . '-' . $i);
        }

        return ['html' => implode('', $baseTokens), 'css' => $this->wrapMedia($css)];
    }

    /**
     * Rules for one target: each device gets the properties that differ from desktop.
     * Mobile also restates properties that tablet changed, since the tablet query matches phones too.
     *
     * @param array<string, string> $base
     * @param array<string, array<string, string>> $deviceStyles device => props (only devices that differ)
     * @param array<int, string> $devices all devices with a variant, in cascade order
     * @return array<string, array<string, string>>
     */
    public function deviceRules(array $base, array $deviceStyles, array $devices): array
    {
        $rules = [];
        $previous = [];
        foreach (array_keys(self::BREAKPOINTS) as $device) {
            if (!in_array($device, $devices, true)) {
                continue;
            }
            $props = $deviceStyles[$device] ?? $base;
            $rule = [];
            foreach ($props as $prop => $value) {
                if (($base[$prop] ?? null) !== $value) {
                    $rule[$prop] = $value;
                }
            }
            foreach ($base as $prop => $value) {
                if (!array_key_exists($prop, $props)) {
                    $rule[$prop] = 'unset';
                }
            }
            // Undo what a wider breakpoint changed but this device keeps as desktop
            foreach ($previous as $prop => $value) {
                if (!array_key_exists($prop, $rule)) {
                    $rule[$prop] = $props[$prop] ?? 'unset';
                }
            }
            if ($rule !== []) {
                $rules[$device] = $rule;
            }
            $previous = $rule + $previous;
        }

        return $rules;
    }

    /**
     * @param array<string, array<int, string>> $rulesByDevice
     */
    public function wrapMedia(array $rulesByDevice): string
    {
        $out = '';
        foreach (self::BREAKPOINTS as $device => $width) {
            if (!empty($rulesByDevice[$device])) {
                $out .= "@media (max-width: {$width}px){" . implode('', $rulesByDevice[$device]) . '}';
            }
        }

        return $out;
    }

    /**
     * @param array<string, string> $props
     */
    public function declarations(array $props): string
    {
        $out = '';
        foreach ($props as $prop => $value) {
            $value = preg_replace('/\s*!important\s*$/i', '', $value);
            $out .= "{$prop}:{$value} !important;";
        }

        return $out;
    }

    /**
     * @return array<string, string> property => value, in source order
     */
    public function parseStyle(string $style): array
    {
        $props = [];
        // Split on ";" outside parentheses/quotes (url(data:...;base64) must stay whole)
        foreach (preg_split('/;(?![^(]*\))(?=(?:[^"\']*["\'][^"\']*["\'])*[^"\']*$)/', $style) as $decl) {
            $pos = strpos($decl, ':');
            if ($pos === false) {
                continue;
            }
            $prop = strtolower(trim(substr($decl, 0, $pos)));
            $value = trim(substr($decl, $pos + 1));
            if ($prop !== '' && $value !== '' && preg_match('/^-?[a-z][a-z0-9-]*$/', $prop)) {
                $props[$prop] = $value;
            }
        }

        return $props;
    }

    /**
     * @return array<int, string>
     */
    private function tokenize(string $html): array
    {
        return preg_split('/(<[^>]+>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
    }

    private function isTag(string $token): bool
    {
        return isset($token[0]) && $token[0] === '<';
    }

    /**
     * @return array{name: string, attrs: string, style: string}|null
     */
    private function parseTag(string $tag): ?array
    {
        if (!preg_match('/^<([a-zA-Z][a-zA-Z0-9-]*)/', $tag, $m)) {
            return null;
        }
        $style = '';
        $rest = preg_replace_callback('/\sstyle\s*=\s*("([^"]*)"|\'([^\']*)\')/i', function ($s) use (&$style) {
            $style = html_entity_decode($s[2] !== '' ? $s[2] : ($s[3] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            return '';
        }, $tag, 1);

        return ['name' => strtolower($m[1]), 'attrs' => (string) $rest, 'style' => $style];
    }

    private function addMarker(string $tag, string $marker): string
    {
        return preg_replace('/^(<[a-zA-Z][a-zA-Z0-9-]*)/', '$1 data-pbr="' . $marker . '"', $tag, 1);
    }
}
