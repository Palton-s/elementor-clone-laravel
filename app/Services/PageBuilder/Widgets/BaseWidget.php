<?php

namespace App\Services\PageBuilder\Widgets;

use App\Contracts\PageBuilder\WidgetInterface;
use App\Services\PageBuilder\Core\ControlTranslator;

abstract class BaseWidget implements WidgetInterface
{
    protected string $type;
    protected string $label;
    protected string $icon;
    protected array $categories = [];
    protected array $defaultSettings = [];
    protected array $controls = [];
    protected array $keywords = [];
    protected bool $container = false;
    protected bool $dynamic = false;

    public function getType(): string
    {
        return $this->type;
    }

    public function getLabel(): string
    {
        return ControlTranslator::widgetLabel($this->type, $this->label);
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getDefaultSettings(): array
    {
        return $this->defaultSettings;
    }

    public function getControls(): array
    {
        return $this->controls;
    }

    public function isContainer(): bool
    {
        return $this->container;
    }

    public function isDynamic(): bool
    {
        return $this->dynamic;
    }

    public function getKeywords(): array
    {
        return $this->keywords;
    }

    public function validateSettings(array $settings): array
    {
        $validated = [];

        foreach ($this->controls as $key => $control) {
            $value = $settings[$key] ?? $this->defaultSettings[$key] ?? null;

            if ($value !== null) {
                $validated[$key] = $this->validateValue($key, $value, $control);
            } elseif (($control['required'] ?? false)) {
                $validated[$key] = $this->defaultSettings[$key] ?? null;
            }
        }

        return array_merge($this->defaultSettings, $validated);
    }

    protected function validateValue(string $key, mixed $value, array $control): mixed
    {
        return match ($control['type'] ?? 'text') {
            'number' => $this->validateNumber($value, $control),
            'select' => $this->validateSelect($value, $control),
            'color' => $this->validateColor($value),
            'url' => $this->validateUrl($value),
            'image' => $this->validateImage($value),
            'dimension' => $this->validateDimension($value, $control),
            'wysiwyg' => (string) $value,
            default => $this->validateText($value, $control),
        };
    }

    protected function validateText(mixed $value, array $control): string
    {
        $value = (string) $value;

        if ($maxLength = $control['max_length'] ?? null) {
            $value = mb_substr($value, 0, $maxLength);
        }

        return strip_tags($value);
    }

    protected function validateNumber(mixed $value, array $control): float
    {
        $value = (float) $value;

        if (isset($control['min'])) {
            $value = max($value, (float) $control['min']);
        }

        if (isset($control['max'])) {
            $value = min($value, (float) $control['max']);
        }

        return $value;
    }

    protected function validateSelect(mixed $value, array $control): string
    {
        $options = $control['options'] ?? [];

        return in_array($value, $options) ? $value : ($control['default'] ?? '');
    }

    protected function validateColor(mixed $value): string
    {
        $value = (string) $value;

        if (preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6}|[a-fA-F0-9]{8})$/', $value)) {
            return $value;
        }

        if (preg_match('/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d*\.?\d+))?\)$/', $value)) {
            return $value;
        }

        $namedColors = ['transparent', 'currentColor', 'inherit', 'initial'];

        return in_array($value, $namedColors) ? $value : '#000000';
    }

    protected function validateUrl(mixed $value): string
    {
        $value = (string) $value;

        if (empty($value)) {
            return '';
        }

        return filter_var($value, FILTER_VALIDATE_URL) ? $value : '';
    }

    protected function validateImage(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $url = $value['url'] ?? '';
        if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
            $url = filter_var($url, FILTER_SANITIZE_URL);
            if ($url !== '' && !str_starts_with($url, 'http://') && !str_starts_with($url, 'https://') && !str_starts_with($url, '/')) {
                $url = '';
            }
        }

        return [
            'url' => $url,
            'alt' => strip_tags($value['alt'] ?? ''),
            'width' => (int) ($value['width'] ?? 0),
            'height' => (int) ($value['height'] ?? 0),
        ];
    }

    protected function validateDimension(mixed $value, array $control): array
    {
        if (!is_array($value)) {
            return $control['default'] ?? [];
        }

        $allowedUnits = $control['units'] ?? ['px', '%', 'em', 'rem', 'vh', 'vw'];

        $result = [];
        foreach (['top', 'right', 'bottom', 'left', 'width', 'height'] as $dim) {
            if (isset($value[$dim])) {
                $val = $value[$dim];
                if (is_string($val)) {
                    $unit = preg_replace('/[\d.]/', '', $val) ?: 'px';
                    $num = (float) $val;
                    $unit = in_array($unit, $allowedUnits) ? $unit : 'px';
                    $result[$dim] = $num . $unit;
                } else {
                    $result[$dim] = (float) $val . 'px';
                }
            }
        }

        return $result;
    }

    protected function mergeStyles(array $base, array $override): array
    {
        return array_merge($base, $override);
    }

    protected function buildStyleAttribute(array $styles): string
    {
        if (empty($styles)) {
            return '';
        }

        $css = '';
        foreach ($styles as $property => $value) {
            $cssProperty = \Illuminate\Support\Str::kebab($property);
            $css .= "{$cssProperty}: {$value}; ";
        }

        return " style=\"{$css}\"";
    }

    protected function prepareSettings(array $settings): array
    {
        return array_merge($this->defaultSettings, $settings);
    }

    protected function safeCssValue(string $value): string
    {
        return str_replace(['<', '>', '"', "'"], '', $value);
    }

    protected function renderTemplate(string $template, array $data = []): string
    {
        $result = $template;

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            $result = str_replace('{{' . $key . '}}', (string) $value, $result);
        }

        return $result;
    }

    protected function buildHoverStyle(string $cssClass, array $styles): string
    {
        if (empty($styles)) return '';

        $hoverCss = '';
        if (!empty($styles['hoverBackgroundColor'])) {
            $hoverCss .= "background-color: {$this->safeCssValue($styles['hoverBackgroundColor'])}; ";
        }
        if (!empty($styles['hoverTextColor'])) {
            $hoverCss .= "color: {$this->safeCssValue($styles['hoverTextColor'])}; ";
        }
        if (!empty($styles['hoverBorderColor'])) {
            $hoverCss .= "border-color: {$this->safeCssValue($styles['hoverBorderColor'])}; ";
        }
        if (!empty($styles['hoverTransform']) && $styles['hoverTransform'] !== 'none') {
            $hoverCss .= "transform: {$this->safeCssValue($styles['hoverTransform'])}; ";
        }
        if (empty($hoverCss)) return '';

        $transition = $styles['hoverTransition'] ?? '300';
        $transition = $this->safeCssValue($transition);

        return "<style>.{$cssClass}:hover { {$hoverCss} transition: all {$transition}ms ease; }</style>";
    }
}
