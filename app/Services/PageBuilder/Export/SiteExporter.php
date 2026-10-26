<?php

namespace App\Services\PageBuilder\Export;

use App\Models\Page;
use App\Models\Project;
use App\Services\PageBuilder\Theme\ThemeService;
use Composer\CaBundle\CaBundle;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * Builds a static, self-contained .zip of one page or a whole project:
 * HTML files (one folder per page) plus every asset that can be collected
 * (uploads, CDN stylesheets/scripts, the fonts and images they reference).
 */
class SiteExporter
{
    private const CATEGORIES = [
        'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif', 'ico', 'bmp', 'tif', 'tiff'],
        'videos' => ['mp4', 'webm', 'ogv', 'mov', 'm4v', 'mkv'],
        'audio' => ['mp3', 'wav', 'ogg', 'oga', 'm4a', 'aac', 'flac'],
        'fonts' => ['woff', 'woff2', 'ttf', 'otf', 'eot'],
        'css' => ['css'],
        'js' => ['js', 'mjs'],
        'data' => ['json', 'lottie', 'xml', 'vtt', 'srt'],
        'docs' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp', 'txt', 'csv', 'rtf', 'zip', 'rar', '7z'],
    ];

    private const MIME_EXT = [
        'text/css' => 'css', 'application/javascript' => 'js', 'text/javascript' => 'js',
        'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp',
        'image/svg+xml' => 'svg', 'image/avif' => 'avif', 'image/x-icon' => 'ico', 'image/vnd.microsoft.icon' => 'ico',
        'font/woff' => 'woff', 'font/woff2' => 'woff2', 'font/ttf' => 'ttf', 'font/otf' => 'otf',
        'application/font-woff' => 'woff', 'application/font-woff2' => 'woff2',
        'video/mp4' => 'mp4', 'video/webm' => 'webm', 'audio/mpeg' => 'mp3', 'audio/ogg' => 'ogg',
        'application/json' => 'json', 'application/pdf' => 'pdf',
    ];

    // Attributes whose value is a single URL
    private const URL_ATTRS = ['src', 'href', 'poster', 'action', 'data', 'data-src', 'data-bg', 'data-background',
        'data-background-image', 'data-poster', 'data-image', 'data-url', 'data-lottie', 'data-video', 'data-href'];

    private ZipArchive $zip;
    /** @var array<string, string|null> absolute url => zip path (null = could not be collected) */
    private array $assets = [];
    /** @var array<string, true> */
    private array $usedPaths = [];
    /** @var array<string, string> url path => zip path of the exported page */
    private array $pageLinks = [];
    /** @var array<int, string> */
    private array $failures = [];
    private int $totalBytes = 0;
    private string $appBase;
    /** @var array<int, string> */
    private array $localHosts = [];

    public function __construct(private ThemeService $themes)
    {
    }

    /**
     * @return string absolute path of the generated zip (caller deletes it)
     */
    public function exportPage(Page $page): string
    {
        return $this->build([['page' => $page, 'path' => 'index.html']], $page->title);
    }

    public function exportProject(Project $project): string
    {
        $pages = $project->pages()->orderBy('created_at')->get();
        if ($pages->isEmpty()) {
            throw new RuntimeException('O projeto não tem páginas para exportar.');
        }

        $homeSlugs = [config('page-builder.theme_builder.home_slug', 'home'), 'home', 'inicio', 'index'];
        $home = $pages->first(fn (Page $p) => in_array($p->slug, $homeSlugs, true)) ?? $pages->first();

        $entries = [['page' => $home, 'path' => 'index.html']];
        $taken = ['assets' => true];
        foreach ($pages as $page) {
            if ($page->is($home)) {
                continue;
            }
            $folder = Str::slug($page->slug ?: $page->title) ?: 'pagina-' . $page->id;
            while (isset($taken[$folder])) {
                $folder .= '-' . $page->id;
            }
            $taken[$folder] = true;
            $entries[] = ['page' => $page, 'path' => $folder . '/index.html'];
        }

        return $this->build($entries, $project->name);
    }

    /**
     * @param array<int, array{page: Page, path: string}> $entries
     */
    private function build(array $entries, string $siteName): string
    {
        @set_time_limit(600);
        $this->reset();

        $dir = storage_path('app/exports');
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Não foi possível criar a pasta de exportação.');
        }
        // Zips are deleted after download; this catches interrupted downloads
        foreach (glob($dir . DIRECTORY_SEPARATOR . '*.zip') ?: [] as $stale) {
            if (filemtime($stale) < time() - 3600) {
                @unlink($stale);
            }
        }
        $zipPath = $dir . DIRECTORY_SEPARATOR . Str::uuid() . '.zip';

        $this->zip = new ZipArchive();
        if ($this->zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Não foi possível criar o arquivo .zip.');
        }

        foreach ($entries as $entry) {
            $this->pageLinks['/p/' . $entry['page']->slug] = $entry['path'];
            $this->pageLinks['/page-builder/pages/' . $entry['page']->id . '/render'] = $entry['path'];
        }

        foreach ($entries as $entry) {
            $html = $this->themes->renderPageWithTheme($entry['page']);
            $this->zip->addFromString($entry['path'], $this->processHtml($html, $entry['path']));
        }

        $this->zip->addFromString('LEIA-ME.txt', $this->readme($siteName, $entries));
        $this->zip->close();

        return $zipPath;
    }

    private function reset(): void
    {
        $this->assets = [];
        $this->usedPaths = ['index.html' => true, 'LEIA-ME.txt' => true];
        $this->pageLinks = [];
        $this->failures = [];
        $this->totalBytes = 0;

        $this->appBase = rtrim(app()->runningInConsole() ? config('app.url') : request()->getSchemeAndHttpHost(), '/');
        $hosts = [parse_url((string) config('app.url'), PHP_URL_HOST), 'localhost', '127.0.0.1'];
        if (!app()->runningInConsole()) {
            $hosts[] = request()->getHost();
        }
        $this->localHosts = array_values(array_unique(array_filter(array_map('strtolower', $hosts))));
    }

    // ------------------------------------------------------------------ HTML

    private function processHtml(string $html, string $htmlPath): string
    {
        $prefix = $this->prefixFor($htmlPath);

        // The CSP meta only makes sense on the server and would block local files
        $html = preg_replace('/<meta\b[^>]*http-equiv\s*=\s*["\']?Content-Security-Policy["\']?[^>]*>\s*/i', '', $html);
        $html = preg_replace('/<meta\b[^>]*name\s*=\s*["\']csrf-token["\'][^>]*>\s*/i', '', $html);

        $html = preg_replace_callback('#(<style\b[^>]*>)(.*?)(</style>)#is', function ($m) use ($prefix) {
            return $m[1] . $this->processCss($m[2], $this->appBase . '/', $prefix) . $m[3];
        }, $html);

        return preg_replace_callback('#<([a-zA-Z][a-zA-Z0-9-]*)(\s[^<>]*?)?(/?)>#s', function ($m) use ($prefix) {
            if (empty($m[2])) {
                return $m[0];
            }
            $tag = strtolower($m[1]);
            $attrs = $this->processAttributes($tag, $m[2], $prefix);
            // A localized file opened from disk (file://) is blocked when requested with CORS,
            // and the rewritten CSS no longer matches an SRI hash: drop both on local assets
            if (in_array($tag, ['link', 'script'], true) && $attrs !== $m[2]
                && preg_match('/\s(?:href|src)\s*=\s*["\'](?:\.\.\/)*assets\//i', $attrs)) {
                $attrs = preg_replace('/\s(?:crossorigin|integrity)(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+))?/i', '', $attrs);
            }
            return '<' . $m[1] . $attrs . $m[3] . '>';
        }, $html);
    }

    private function processAttributes(string $tag, string $attrs, string $prefix): string
    {
        $rel = '';
        if ($tag === 'link' && preg_match('/\srel\s*=\s*["\']?([^"\'>]+)/i', $attrs, $r)) {
            $rel = strtolower($r[1]);
        }

        return preg_replace_callback('/(\s)([a-zA-Z_:][-a-zA-Z0-9_:.]*)(\s*=\s*)(?:"([^"]*)"|\'([^\']*)\')/s', function ($m) use ($tag, $rel, $prefix) {
            $name = strtolower($m[2]);
            $quote = isset($m[5]) ? "'" : '"';
            $raw = isset($m[5]) ? $m[5] : $m[4];
            $value = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $new = match (true) {
                $name === 'style' => $this->processCss($value, $this->appBase . '/', $prefix),
                $name === 'srcset' || $name === 'data-srcset' => $this->processSrcset($value, $prefix),
                in_array($name, self::URL_ATTRS, true) => $this->processUrlAttr($tag, $name, $rel, $value, $prefix),
                default => null,
            };

            if ($new === null || $new === $value) {
                return $m[0];
            }

            return $m[1] . $m[2] . $m[3] . $quote . htmlspecialchars($new, ENT_QUOTES | ENT_HTML5, 'UTF-8', false) . $quote;
        }, $attrs);
    }

    private function processUrlAttr(string $tag, string $name, string $rel, string $value, string $prefix): ?string
    {
        $value = trim($value);
        if ($this->isNonFetchable($value)) {
            return null;
        }

        // Embeds (YouTube, Vimeo, maps) keep their online URL; iframes pointing at a file (mp4, pdf) are collected
        if (in_array($tag, ['iframe', 'frame'], true) && !$this->hasAssetExtension($value)) {
            return $this->absolutize($value);
        }

        if ($name === 'action') {
            return $this->absolutize($value);
        }

        if ($name === 'href' || $name === 'data-href') {
            $pageLink = $this->matchPageLink($value);
            if ($pageLink !== null) {
                return $prefix . $pageLink;
            }

            $isLinkAsset = $tag === 'link' && preg_match('/stylesheet|icon|preload|prefetch|manifest|apple-touch/', $rel);
            if (!$isLinkAsset && !$this->hasAssetExtension($value)) {
                return $this->absolutize($value);
            }
        }

        $hint = match (true) {
            $tag === 'link' && str_contains($rel, 'stylesheet') => 'css',
            $tag === 'script' => 'js',
            default => null,
        };

        $zipPath = $this->collect($value, $this->appBase . '/', $hint);

        return $zipPath !== null ? $prefix . $zipPath : $this->absolutize($value);
    }

    private function processSrcset(string $value, string $prefix): string
    {
        $parts = array_map('trim', explode(',', $value));
        foreach ($parts as &$part) {
            if ($part === '') {
                continue;
            }
            $bits = preg_split('/\s+/', $part, 2);
            $zipPath = $this->isNonFetchable($bits[0]) ? null : $this->collect($bits[0], $this->appBase . '/', null);
            $url = $zipPath !== null ? $prefix . $zipPath : $this->absolutize($bits[0]);
            $part = $url . (isset($bits[1]) ? ' ' . $bits[1] : '');
        }

        return implode(', ', $parts);
    }

    // ------------------------------------------------------------------- CSS

    private function processCss(string $css, string $baseUrl, string $prefix): string
    {
        // @import "file.css";  (the url(...) form is handled below)
        $css = preg_replace_callback('/@import\s+(["\'])([^"\']+)\1/i', function ($m) use ($baseUrl, $prefix) {
            $zipPath = $this->collect($m[2], $baseUrl, 'css');
            return '@import ' . $m[1] . ($zipPath !== null ? $prefix . $zipPath : $this->resolve($m[2], $baseUrl) ?? $m[2]) . $m[1];
        }, $css);

        return preg_replace_callback('/url\(\s*(["\']?)([^)"\']*)\1\s*\)/i', function ($m) use ($baseUrl, $prefix) {
            $url = trim($m[2]);
            if ($url === '' || $this->isNonFetchable($url)) {
                return $m[0];
            }
            $fragment = '';
            if (($hash = strpos($url, '#')) !== false) {
                $fragment = substr($url, $hash);
                $url = substr($url, 0, $hash);
            }
            $zipPath = $this->collect($url, $baseUrl, null);
            $out = $zipPath !== null ? $prefix . $zipPath . $fragment : ($this->resolve($url, $baseUrl) ?? $url) . $fragment;

            return 'url("' . str_replace('"', '%22', $out) . '")';
        }, $css);
    }

    // ---------------------------------------------------------------- assets

    /**
     * Adds the asset to the zip (once) and returns its path inside the zip, or null.
     */
    private function collect(string $url, string $baseUrl, ?string $hint): ?string
    {
        $absolute = $this->resolve($url, $baseUrl);
        if ($absolute === null) {
            return null;
        }
        $key = preg_replace('/#.*$/', '', $absolute);
        if (array_key_exists($key, $this->assets)) {
            return $this->assets[$key];
        }
        $this->assets[$key] = null;

        $parts = parse_url($key);
        $host = strtolower($parts['host'] ?? '');
        $path = rawurldecode($parts['path'] ?? '/');

        if (in_array($host, $this->localHosts, true)) {
            $file = $this->localFile($path);
            if ($file === null) {
                return null;
            }
            $zipPath = $this->addLocalFile($file, $path, $hint);
        } else {
            $zipPath = $this->addRemoteFile($key, $host, $path, $hint);
        }

        return $this->assets[$key] = $zipPath;
    }

    private function addLocalFile(string $file, string $urlPath, ?string $hint): ?string
    {
        $size = (int) filesize($file);
        if (!$this->fitsBudget($size, $urlPath)) {
            return null;
        }

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $category = $hint ?? $this->categoryFor($ext);
        // Keep the original sub-folders (e.g. page-builder/videos) below the category folder
        $relative = ltrim(preg_replace('#^/(storage|uploads)/#', '', $urlPath), '/');
        $zipPath = $this->uniquePath('assets/' . $category . '/' . $this->safePath($relative));
        $this->totalBytes += $size;

        if ($category === 'css') {
            $base = $this->appBase . '/' . ltrim($urlPath, '/');
            $this->assets[preg_replace('/#.*$/', '', $base)] = $zipPath;
            $this->zip->addFromString($zipPath, $this->processCss((string) file_get_contents($file), $base, $this->prefixFor($zipPath)));
        } else {
            $this->zip->addFile($file, $zipPath);
        }

        return $zipPath;
    }

    private function addRemoteFile(string $url, string $host, string $path, ?string $hint): ?string
    {
        if (!config('page-builder.export.download_remote', true)) {
            return null;
        }

        $download = $this->download($url);
        if ($download === null) {
            return null;
        }
        [$body, $contentType] = $download;

        if (!$this->fitsBudget(strlen($body), $url)) {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === '' || !$this->categoryFor($ext, false)) {
            $ext = self::MIME_EXT[strtolower(trim(explode(';', $contentType)[0]))] ?? ($hint ?? $ext);
        }
        $category = $hint ?? $this->categoryFor($ext);

        $name = pathinfo($path, PATHINFO_FILENAME) ?: 'arquivo';
        $zipPath = $this->uniquePath('assets/' . $category . '/' . $this->safePath($host) . '/' . $this->safePath($name) . ($ext ? '.' . $ext : ''));
        $this->assets[$url] = $zipPath;
        $this->totalBytes += strlen($body);

        if ($category === 'css') {
            $body = $this->processCss($body, $url, $this->prefixFor($zipPath));
        }
        $this->zip->addFromString($zipPath, $body);

        return $zipPath;
    }

    /**
     * @return array{0: string, 1: string}|null [body, content-type]
     */
    private function download(string $url): ?array
    {
        $cacheDir = storage_path('app/export-cache');
        $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . md5($url);
        $maxAge = (int) config('page-builder.export.cache_days', 7) * 86400;
        if (is_file($cacheFile) && is_file($cacheFile . '.type') && filemtime($cacheFile) > time() - $maxAge) {
            return [(string) file_get_contents($cacheFile), (string) file_get_contents($cacheFile . '.type')];
        }

        $current = $url;
        for ($hop = 0; $hop < 4; $hop++) {
            $host = parse_url($current, PHP_URL_HOST);
            $scheme = strtolower((string) parse_url($current, PHP_URL_SCHEME));
            if (!in_array($scheme, ['http', 'https'], true) || !$host || !$this->isPublicHost($host)) {
                $this->failures[] = "Bloqueado (endereço não público): {$url}";
                return null;
            }

            try {
                $response = Http::timeout((int) config('page-builder.export.remote_timeout', 20))
                    // CaBundle finds the system CA store, or falls back to Mozilla's bundle (many Windows PHP builds ship none)
                    ->withOptions(['allow_redirects' => false, 'verify' => CaBundle::getSystemCaRootBundlePath()])
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (PageBuilder site export)'])
                    ->get($current);
            } catch (\Throwable $e) {
                $this->failures[] = "Falha ao baixar: {$url}";
                return null;
            }

            if ($response->redirect() && $response->header('Location')) {
                $current = $this->resolve($response->header('Location'), $current) ?? '';
                continue;
            }
            if (!$response->successful()) {
                $this->failures[] = "HTTP {$response->status()}: {$url}";
                return null;
            }

            $body = $response->body();
            $type = (string) $response->header('Content-Type');
            if (!is_dir($cacheDir)) {
                @mkdir($cacheDir, 0775, true);
            }
            @file_put_contents($cacheFile, $body);
            @file_put_contents($cacheFile . '.type', $type);

            return [$body, $type];
        }

        $this->failures[] = "Redirecionamentos demais: {$url}";
        return null;
    }

    /**
     * Prevents the export from being used to reach internal services (SSRF).
     */
    protected function isPublicHost(string $host): bool
    {
        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : (gethostbynamel($host) ?: []);
        if ($ips === []) {
            return false;
        }
        foreach ($ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Maps a local URL path to a file inside public/ or the public storage disk.
     */
    private function localFile(string $path): ?string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === '' || !$this->categoryFor($ext, false) || str_contains($path, "\0")) {
            return null;
        }

        $candidates = [];
        if (preg_match('#^/storage/(.+)$#', $path, $m)) {
            $candidates[] = [Storage::disk('public')->path($m[1]), Storage::disk('public')->path('')];
        }
        $candidates[] = [public_path(ltrim($path, '/')), public_path()];

        foreach ($candidates as [$file, $root]) {
            $real = realpath($file);
            $realRoot = realpath($root);
            if ($real && $realRoot && is_file($real) && str_starts_with($real, rtrim($realRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)) {
                return $real;
            }
        }

        return null;
    }

    // --------------------------------------------------------------- helpers

    private function resolve(string $url, string $baseUrl): ?string
    {
        $url = trim($url);
        if ($url === '' || $this->isNonFetchable($url)) {
            return null;
        }
        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }
        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $url)) {
            return null;
        }

        $base = parse_url($baseUrl);
        $origin = ($base['scheme'] ?? 'http') . '://' . ($base['host'] ?? '') . (isset($base['port']) ? ':' . $base['port'] : '');
        if (str_starts_with($url, '/')) {
            return $origin . $this->normalizePath($url);
        }

        $dir = preg_replace('#/[^/]*$#', '/', $base['path'] ?? '/');
        return $origin . $this->normalizePath($dir . $url);
    }

    private function normalizePath(string $path): string
    {
        $query = '';
        if (($q = strpos($path, '?')) !== false) {
            $query = substr($path, $q);
            $path = substr($path, 0, $q);
        }
        $out = [];
        foreach (explode('/', $path) as $seg) {
            if ($seg === '..') {
                array_pop($out);
            } elseif ($seg !== '.') {
                $out[] = $seg;
            }
        }

        return '/' . ltrim(implode('/', $out), '/') . $query;
    }

    private function absolutize(string $url): string
    {
        return str_starts_with($url, '/') && !str_starts_with($url, '//') ? $this->appBase . $url : $url;
    }

    private function matchPageLink(string $url): ?string
    {
        $parts = parse_url($url);
        if ($parts === false) {
            return null;
        }
        $host = strtolower($parts['host'] ?? '');
        if ($host !== '' && !in_array($host, $this->localHosts, true)) {
            return null;
        }
        $path = rtrim($parts['path'] ?? '', '/');
        if (!isset($this->pageLinks[$path])) {
            return null;
        }

        return $this->pageLinks[$path] . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    }

    private function isNonFetchable(string $url): bool
    {
        return $url === ''
            || str_starts_with($url, '#')
            || str_contains($url, '{{') || str_contains($url, '${')
            || (bool) preg_match('#^(data|blob|mailto|tel|javascript|about|sms):#i', $url);
    }

    private function hasAssetExtension(string $url): bool
    {
        $ext = strtolower(pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

        return $ext !== '' && $ext !== 'css' && $ext !== 'js' && $this->categoryFor($ext, false) !== null;
    }

    private function categoryFor(string $ext, bool $fallback = true): ?string
    {
        foreach (self::CATEGORIES as $category => $exts) {
            if (in_array($ext, $exts, true)) {
                return $category;
            }
        }

        return $fallback ? 'outros' : null;
    }

    private function fitsBudget(int $size, string $source): bool
    {
        $maxFile = (int) config('page-builder.export.max_file_mb', 200) * 1024 * 1024;
        $maxTotal = (int) config('page-builder.export.max_total_mb', 1024) * 1024 * 1024;
        if ($size > $maxFile) {
            $this->failures[] = "Arquivo grande demais (mantido online): {$source}";
            return false;
        }
        if ($this->totalBytes + $size > $maxTotal) {
            $this->failures[] = "Limite total do .zip atingido (mantido online): {$source}";
            return false;
        }

        return true;
    }

    private function safePath(string $path): string
    {
        $segments = array_filter(explode('/', str_replace('\\', '/', $path)), fn ($s) => $s !== '' && $s !== '.' && $s !== '..');
        $segments = array_map(function ($s) {
            $s = preg_replace('/[^A-Za-z0-9._-]+/', '-', Str::ascii($s));
            return trim($s, '-') ?: 'arquivo';
        }, $segments);

        return implode('/', $segments) ?: 'arquivo';
    }

    private function uniquePath(string $path): string
    {
        $candidate = $path;
        $i = 2;
        while (isset($this->usedPaths[strtolower($candidate)])) {
            $info = pathinfo($path);
            $candidate = ($info['dirname'] !== '.' ? $info['dirname'] . '/' : '') . $info['filename'] . '-' . $i++ . (isset($info['extension']) ? '.' . $info['extension'] : '');
        }
        $this->usedPaths[strtolower($candidate)] = true;

        return $candidate;
    }

    private function prefixFor(string $zipPath): string
    {
        return str_repeat('../', substr_count($zipPath, '/'));
    }

    /**
     * @param array<int, array{page: Page, path: string}> $entries
     */
    private function readme(string $siteName, array $entries): string
    {
        $lines = [
            $siteName,
            str_repeat('=', max(3, mb_strlen($siteName))),
            '',
            'Exportado em ' . now()->format('d/m/Y H:i'),
            '',
            'Abra o arquivo index.html no navegador ou envie a pasta inteira para qualquer hospedagem estática.',
            '',
            'Páginas:',
        ];
        foreach ($entries as $entry) {
            $lines[] = '  - ' . $entry['page']->title . '  ->  ' . $entry['path'];
        }
        $lines[] = '';
        $lines[] = 'Arquivos: ' . count(array_filter($this->assets)) . ' asset(s) em assets/ (images, videos, audio, fonts, css, js, data, docs).';
        $lines[] = 'Vídeos do YouTube/Vimeo, mapas e o envio de formulários continuam precisando de internet e do servidor original.';

        if ($this->failures !== []) {
            $lines[] = '';
            $lines[] = 'Não foi possível incluir (o link original foi mantido):';
            foreach (array_unique($this->failures) as $failure) {
                $lines[] = '  - ' . $failure;
            }
        }

        return implode("\r\n", $lines) . "\r\n";
    }
}
