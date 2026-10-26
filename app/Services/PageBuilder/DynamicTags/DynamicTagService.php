<?php

namespace App\Services\PageBuilder\DynamicTags;

class DynamicTagService
{
    protected array $tagGroups = [];

    public function __construct()
    {
        $this->registerTags();
    }

    protected function registerTags(): void
    {
        $this->tagGroups = [
            'page' => [
                'label' => 'Page',
                'tags' => [
                    ['tag' => 'page.title', 'label' => 'Page Title', 'description' => 'The page title'],
                    ['tag' => 'page.slug', 'label' => 'Page Slug', 'description' => 'The page URL slug'],
                    ['tag' => 'page.id', 'label' => 'Page ID', 'description' => 'The page ID'],
                    ['tag' => 'page.url', 'label' => 'Page URL', 'description' => 'Full page URL'],
                    ['tag' => 'page.status', 'label' => 'Page Status', 'description' => 'Published/Draft status'],
                ],
            ],
            'site' => [
                'label' => 'Site',
                'tags' => [
                    ['tag' => 'site.name', 'label' => 'Site Name', 'description' => 'Application name'],
                    ['tag' => 'site.url', 'label' => 'Site URL', 'description' => 'Base site URL'],
                    ['tag' => 'site.description', 'label' => 'Site Description', 'description' => 'Site tagline/description'],
                ],
            ],
            'user' => [
                'label' => 'User',
                'tags' => [
                    ['tag' => 'user.name', 'label' => 'User Name', 'description' => 'Current user display name'],
                    ['tag' => 'user.email', 'label' => 'User Email', 'description' => 'Current user email'],
                    ['tag' => 'user.id', 'label' => 'User ID', 'description' => 'Current user ID'],
                    ['tag' => 'user.role', 'label' => 'User Role', 'description' => 'Current user role'],
                ],
            ],
            'date' => [
                'label' => 'Date & Time',
                'tags' => [
                    ['tag' => 'date', 'label' => 'Current Date', 'description' => 'Today\'s date (e.g. Jan 15, 2026)'],
                    ['tag' => 'time', 'label' => 'Current Time', 'description' => 'Current time (e.g. 2:30 PM)'],
                    ['tag' => 'year', 'label' => 'Current Year', 'description' => 'Current year (e.g. 2026)'],
                    ['tag' => 'month', 'label' => 'Current Month', 'description' => 'Current month name'],
                    ['tag' => 'day', 'label' => 'Current Day', 'description' => 'Current day number'],
                ],
            ],
        ];
    }

    public function getTagGroups(): array { return $this->tagGroups; }

    public function getFlatTags(): array
    {
        $flat = [];
        foreach ($this->tagGroups as $group) {
            foreach ($group['tags'] as $tag) {
                $flat[] = $tag;
            }
        }
        return $flat;
    }

    public function processTags(string $content, ?array $context = []): string
    {
        $replacements = $this->getReplacements($context);
        $content = preg_replace_callback('/\{\{\s*([a-zA-Z_.]+)\s*\}\}/', function ($matches) use ($replacements) {
            $tag = trim($matches[1]);
            // Tags are expanded inside rendered HTML: values (page title, user name...) are text
            return isset($replacements[$tag])
                ? htmlspecialchars((string) $replacements[$tag], ENT_QUOTES | ENT_HTML5, 'UTF-8', false)
                : $matches[0];
        }, $content);
        return $content;
    }

    public function getReplacements(?array $context = []): array
    {
        $page = $context['page'] ?? null;
        $user = $context['user'] ?? null;

        return [
            'page.title' => $page ? $page->title : '',
            'page.slug' => $page ? $page->slug : '',
            'page.id' => $page ? (string) $page->id : '',
            'page.url' => $page ? url('/p/' . $page->slug) : '',
            'page.status' => $page ? $page->status : '',
            'site.name' => config('app.name', 'Site Name'),
            'site.url' => url('/'),
            'site.description' => config('app.description', 'A page builder site'),
            'user.name' => $user ? $user->name : '',
            'user.email' => $user ? $user->email : '',
            'user.id' => $user ? (string) $user->id : '',
            'user.role' => $user ? ($user->role ?? '') : '',
            'date' => now()->format('M j, Y'),
            'time' => now()->format('g:i A'),
            'year' => now()->format('Y'),
            'month' => now()->format('F'),
            'day' => now()->format('j'),
        ];
    }
}
