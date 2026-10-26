<?php

namespace App\Services\PageBuilder\Core;

/**
 * Translates what the editor displays for widgets (names, control labels, sections and
 * option texts) using lang/{locale}/page_builder.php. Option VALUES are never changed:
 * they are stored in the database and used as CSS, so the editor gets a separate
 * option_labels map for display.
 */
class ControlTranslator
{
    private static array $cache = [];

    private static function dict(string $group): array
    {
        // Widgets can be used outside a booted app (plain unit tests): no translation then
        if (!function_exists('app') || !app()->bound('translator')) {
            return [];
        }
        $locale = app()->getLocale();
        if (!isset(self::$cache[$locale])) {
            $all = trans('page_builder');
            self::$cache[$locale] = is_array($all) ? $all : [];
        }

        return self::$cache[$locale][$group] ?? [];
    }

    public static function widgetLabel(string $type, string $fallback): string
    {
        return self::dict('widgets')[$type] ?? $fallback;
    }

    public static function text(string $group, string $text): string
    {
        return self::dict($group)[$text] ?? $text;
    }

    public static function controls(array $controls): array
    {
        foreach ($controls as $key => $control) {
            if (!is_array($control)) {
                continue;
            }
            if (isset($control['label']) && is_string($control['label'])) {
                $control['label'] = self::text('labels', $control['label']);
            }
            if (isset($control['section']) && is_string($control['section'])) {
                $control['section'] = self::text('sections', $control['section']);
            }
            if (isset($control['options']) && is_array($control['options'])) {
                $labels = [];
                foreach ($control['options'] as $optKey => $optValue) {
                    // ['left', 'right'] lists the values; ['row' => 'Horizontal'] maps value => text
                    $value = is_int($optKey) ? (string) $optValue : (string) $optKey;
                    $labels[$value] = self::text('options', (string) $optValue);
                }
                $control['option_labels'] = $labels;
            }
            if (isset($control['fields']) && is_array($control['fields'])) {
                $control['fields'] = self::controls($control['fields']);
            }
            $controls[$key] = $control;
        }

        return $controls;
    }

    /** For tests: forget the loaded dictionary (e.g. after changing locale). */
    public static function flush(): void
    {
        self::$cache = [];
    }
}
