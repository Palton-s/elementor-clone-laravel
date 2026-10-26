<?php

namespace App\Services\PageBuilder\Theme;

use App\Models\Page;
use App\Models\ThemeTemplate;
use App\Services\PageBuilder\Core\Renderer;
use Illuminate\Support\Facades\Cache;

class ThemeService
{
    protected Renderer $renderer;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function resolveTemplate(string $type, ?string $pageSlug = null): ?ThemeTemplate
    {
        $templates = $this->getPublishedTemplates($type);

        foreach ($templates as $template) {
            if ($this->matchConditions($template, $pageSlug)) {
                return $template;
            }
        }

        return $templates->first();
    }

    public function resolveAllForPage(?string $pageSlug = null): array
    {
        $types = ['header', 'footer'];
        $resolved = [];

        foreach ($types as $type) {
            $resolved[$type] = $this->resolveTemplate($type, $pageSlug);
        }

        return $resolved;
    }

    public function renderTemplate(?ThemeTemplate $template): string
    {
        if (!$template || !$template->isPublished()) {
            return '';
        }

        $page = $template->page;
        if (!$page) {
            return '';
        }

        return $this->renderer->render($page, [
            'with_container' => false,
            'theme' => 'theme-template-' . $template->type,
        ]);
    }

    public function renderPageWithTheme(Page $page): string
    {
        $pageHtml = $this->renderer->render($page, [
            'with_container' => false,
            'theme' => 'page',
        ]);

        $templates = $this->resolveAllForPage($page->slug);

        $headerHtml = $this->renderTemplate($templates['header'] ?? null);
        $footerHtml = $this->renderTemplate($templates['footer'] ?? null);

        $pageSettings = $page->settings ?? [];
        $containerWidth = $pageSettings['container_width'] ?? '1140px';
        $pageBackground = $pageSettings['page_background'] ?? '#ffffff';
        $contentPadding = $pageSettings['content_padding'] ?? '0px';

        $title = htmlspecialchars($page->title, ENT_QUOTES, 'UTF-8');
        $csrfToken = csrf_token();

        $hasMath = $this->renderer->hasMathElements($page);

        $styles = $this->renderer->renderStyles($page, $hasMath);
        $scripts = $this->renderer->renderScripts($page, $hasMath);
        $pageInnerStyle = "max-width: {$containerWidth}; margin: 0 auto; padding: {$contentPadding};";

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{$csrfToken}">
    <title>{$title}</title>
    <style>
        body { margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; background: {$pageBackground}; }
        * { box-sizing: border-box; }
        .pb-page-inner { {$pageInnerStyle} }
    </style>
    {$styles}
    {$scripts}
</head>
<body>
    {$headerHtml}
    <div class="pb-page-inner">
        {$pageHtml}
    </div>
    {$footerHtml}
</body>
</html>
HTML;
    }

    public function matchConditions(ThemeTemplate $template, ?string $pageSlug = null): bool
    {
        $conditions = $template->conditions ?? [];

        if (empty($conditions)) {
            return true;
        }

        foreach ($conditions as $condition) {
            $type = $condition['type'] ?? '';
            $value = $condition['value'] ?? null;

            if ($this->evaluateCondition($type, $value, $pageSlug)) {
                return true;
            }
        }

        return false;
    }

    protected function evaluateCondition(string $type, mixed $value, ?string $pageSlug = null): bool
    {
        return match ($type) {
            'all' => true,
            'home' => $pageSlug === null || $pageSlug === config('page-builder.theme_builder.home_slug', 'home'),
            'all_pages' => $pageSlug !== null,
            'singular' => $pageSlug !== null,
            'specific' => $value && $pageSlug === $value,
            '404' => false,
            'search' => false,
            default => false,
        };
    }

    public function getPublishedTemplates(string $type)
    {
        return Cache::remember("theme_templates_{$type}", 300, function () use ($type) {
            return ThemeTemplate::published()
                ->byType($type)
                ->ordered()
                ->with('page')
                ->get();
        });
    }

    public function clearCache(): void
    {
        foreach (ThemeTemplate::types() as $type) {
            Cache::forget("theme_templates_{$type}");
        }
    }

    public static function conditionOptions(): array
    {
        return [
            'all' => 'Site inteiro',
            'home' => 'Página inicial',
            'all_pages' => 'Todas as páginas',
            'singular' => 'Todas as páginas individuais',
            'specific' => 'Página específica',
        ];
    }
}
