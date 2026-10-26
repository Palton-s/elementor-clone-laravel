<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\CustomFont;
use App\Services\PageBuilder\Core\PageBuilderService;
use App\Services\PageBuilder\Core\Renderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CustomFontController extends Controller
{
    protected PageBuilderService $pageBuilder;
    protected Renderer $renderer;

    public function __construct(
        PageBuilderService $pageBuilder,
        Renderer $renderer,
    ) {
        $this->pageBuilder = $pageBuilder;
        $this->renderer = $renderer;
    }

    public function index(): JsonResponse
    {
        $user = auth()->user();
        $fonts = CustomFont::global()
            ->when(!$user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('family')
            ->get();
        return response()->json($fonts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'family' => 'required|string|max:255',
            'weight' => 'nullable|string|max:50',
            'style' => 'nullable|string|in:normal,italic,oblique',
            'file_ttf' => 'nullable|file|mimes:ttf,otf|max:' . config('page-builder.fonts.max_file_size'),
            'file_woff' => 'nullable|file|mimes:woff|max:' . config('page-builder.fonts.max_file_size'),
            'file_woff2' => 'nullable|file|mimes:woff2|max:' . config('page-builder.fonts.max_file_size'),
            'url_ttf' => 'nullable|url|max:2048',
            'url_woff' => 'nullable|url|max:2048',
            'url_woff2' => 'nullable|url|max:2048',
            'font_display' => 'nullable|string|in:auto,block,swap,fallback,optional',
            'is_global' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $data = [
            'user_id' => $user->id,
            'name' => $validated['name'],
            'family' => $validated['family'],
            'weight' => $validated['weight'] ?? '400',
            'style' => $validated['style'] ?? 'normal',
            'font_display' => $validated['font_display'] ?? 'swap',
            'is_global' => $validated['is_global'] ?? true,
        ];

        $path = config('page-builder.fonts.path');

        foreach (['ttf', 'woff', 'woff2'] as $format) {
            $key = "file_{$format}";
            $urlKey = "url_{$format}";

            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $filename = $validated['family'] . '-' . $validated['weight'] . '-' . $validated['style'] . '.' . $format;
                $filename = preg_replace('/[^a-zA-Z0-9\-\.]/', '-', $filename);
                $data[$key] = $file->storeAs($path, $filename, 'public');
            }

            if ($request->filled($urlKey)) {
                $data[$urlKey] = $request->input($urlKey);
            }
        }

        $font = CustomFont::create($data);

        return response()->json([
            'message' => 'Fonte enviada!',
            'font' => $font,
        ], 201);
    }

    public function update(Request $request, CustomFont $customFont): JsonResponse
    {
        $this->authorizeOwner($customFont);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'family' => 'sometimes|string|max:255',
            'weight' => 'nullable|string|max:50',
            'style' => 'nullable|string|in:normal,italic,oblique',
            'url_ttf' => 'nullable|url|max:2048',
            'url_woff' => 'nullable|url|max:2048',
            'url_woff2' => 'nullable|url|max:2048',
            'font_display' => 'nullable|string|in:auto,block,swap,fallback,optional',
            'is_global' => 'nullable|boolean',
        ]);

        $customFont->update($validated);

        return response()->json([
            'message' => 'Fonte atualizada!',
            'font' => $customFont->fresh(),
        ]);
    }

    public function destroy(CustomFont $customFont): JsonResponse
    {
        $this->authorizeOwner($customFont);
        $path = config('page-builder.fonts.path');

        foreach (['ttf', 'woff', 'woff2'] as $format) {
            $fileField = "file_{$format}";
            if ($customFont->$fileField) {
                Storage::disk('public')->delete($customFont->$fileField);
            }
        }

        $customFont->delete();

        return response()->json([
            'message' => 'Fonte excluída!',
        ]);
    }

    public function download(CustomFont $customFont, string $format)
    {
        $this->authorizeOwner($customFont);

        if (!in_array($format, ['ttf', 'woff', 'woff2'])) {
            abort(404);
        }

        $fileField = "file_{$format}";
        $urlField = "url_{$format}";

        if ($customFont->$fileField) {
            $path = $customFont->$fileField;
            if (!Storage::disk('public')->exists($path)) {
                abort(404);
            }
            return Storage::disk('public')->download($path);
        }

        if ($customFont->$urlField) {
            $url = $customFont->$urlField;
            $scheme = parse_url($url, PHP_URL_SCHEME);

            if (!in_array($scheme, ['http', 'https'], true)) {
                abort(404);
            }

            return redirect()->away($url);
        }

        abort(404);
    }
}
