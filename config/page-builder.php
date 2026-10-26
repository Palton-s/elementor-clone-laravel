<?php

return [
    'template_cache' => [
        'enabled' => env('PAGE_BUILDER_CACHE', false),
        'ttl' => env('PAGE_BUILDER_CACHE_TTL', 3600),
    ],

    'revisions' => [
        'max_per_page' => env('PAGE_BUILDER_MAX_REVISIONS', 50),
        'auto_save_interval' => env('PAGE_BUILDER_AUTO_SAVE_INTERVAL', 60),
    ],

    'widgets' => [
        \App\Services\PageBuilder\Widgets\HeadingWidget::class,
        \App\Services\PageBuilder\Widgets\TextWidget::class,
        \App\Services\PageBuilder\Widgets\ImageWidget::class,
        \App\Services\PageBuilder\Widgets\ButtonWidget::class,
        \App\Services\PageBuilder\Widgets\SectionWidget::class,
        \App\Services\PageBuilder\Widgets\ColumnWidget::class,
        \App\Services\PageBuilder\Widgets\CalloutWidget::class,
        \App\Services\PageBuilder\Widgets\TableWidget::class,
        \App\Services\PageBuilder\Widgets\MathWidget::class,
        \App\Services\PageBuilder\Widgets\VideoWidget::class,
        \App\Services\PageBuilder\Widgets\DividerWidget::class,
        \App\Services\PageBuilder\Widgets\SpacerWidget::class,
        \App\Services\PageBuilder\Widgets\IconWidget::class,
        \App\Services\PageBuilder\Widgets\GalleryWidget::class,
        \App\Services\PageBuilder\Widgets\FormWidget::class,
        \App\Services\PageBuilder\Widgets\TabsWidget::class,
        \App\Services\PageBuilder\Widgets\AccordionWidget::class,
        \App\Services\PageBuilder\Widgets\CounterWidget::class,
        \App\Services\PageBuilder\Widgets\ProgressBarWidget::class,
        \App\Services\PageBuilder\Widgets\SocialIconsWidget::class,
        \App\Services\PageBuilder\Widgets\IconBoxWidget::class,
        \App\Services\PageBuilder\Widgets\ImageBoxWidget::class,
        \App\Services\PageBuilder\Widgets\TestimonialWidget::class,
        \App\Services\PageBuilder\Widgets\PriceTableWidget::class,
        \App\Services\PageBuilder\Widgets\CountdownWidget::class,
        \App\Services\PageBuilder\Widgets\GoogleMapsWidget::class,
        \App\Services\PageBuilder\Widgets\CarouselWidget::class,
        \App\Services\PageBuilder\Widgets\InnerSectionWidget::class,
        \App\Services\PageBuilder\Widgets\MegaMenuWidget::class,
        \App\Services\PageBuilder\Widgets\LottieWidget::class,
    ],

    'upload' => [
        'max_file_size' => env('PAGE_BUILDER_MAX_FILE_SIZE', 10240),
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'mp4', 'webm'],
        'path' => 'page-builder',
    ],

    'editor' => [
        'min_width' => 320,
        'max_width' => 1920,
        'breakpoints' => [
            'mobile' => 768,
            'tablet' => 1024,
            'desktop' => 1920,
        ],
    ],

    'fonts' => [
        'max_file_size' => env('PAGE_BUILDER_FONT_MAX_SIZE', 5120),
        'allowed_types' => ['ttf', 'woff', 'woff2'],
        'path' => 'page-builder/fonts',
    ],

    'theme_builder' => [
        'enabled' => env('PAGE_BUILDER_THEME_BUILDER', true),
        'home_slug' => env('PAGE_BUILDER_HOME_SLUG', 'home'),
        'cache_ttl' => env('PAGE_BUILDER_THEME_CACHE_TTL', 300),
    ],

    'export' => [
        // Download CDN/external assets (CSS, JS, fonts, images) into the zip so the site works offline
        'download_remote' => env('PAGE_BUILDER_EXPORT_REMOTE', true),
        'remote_timeout' => env('PAGE_BUILDER_EXPORT_TIMEOUT', 20),
        'max_file_mb' => env('PAGE_BUILDER_EXPORT_MAX_FILE_MB', 200),
        'max_total_mb' => env('PAGE_BUILDER_EXPORT_MAX_TOTAL_MB', 1024),
        'cache_days' => env('PAGE_BUILDER_EXPORT_CACHE_DAYS', 7),
    ],
];
