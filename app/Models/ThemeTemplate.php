<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ThemeTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'project_id',
        'page_id',
        'title',
        'slug',
        'type',
        'status',
        'conditions',
        'order',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ThemeTemplate $template) {
            if (!$template->slug) {
                $template->slug = Str::slug($template->title) . '-' . Str::random(6);
            }
        });
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function page(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('created_at', 'desc');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'header' => 'Cabeçalho',
            'footer' => 'Rodapé',
            'single' => 'Artigo',
            'archive' => 'Listagem',
            '404' => 'Página 404',
            'search_results' => 'Resultados de busca',
            default => ucfirst($this->type),
        };
    }

    public function getTypeIcon(): string
    {
        return match ($this->type) {
            'header' => "\u{1F3F0}",
            'footer' => "\u{1F3E0}",
            'single' => "\u{1F4D6}",
            'archive' => "\u{1F4CB}",
            '404' => "\u{2753}",
            'search_results' => "\u{1F50D}",
            default => "\u{1F4C4}",
        };
    }

    public static function types(): array
    {
        return ['header', 'footer', 'single', 'archive', '404', 'search_results'];
    }

    public static function typeOptions(): array
    {
        return [
            'header' => 'Cabeçalho',
            'footer' => 'Rodapé',
            'single' => 'Artigo',
            'archive' => 'Listagem',
            '404' => 'Página 404',
            'search_results' => 'Resultados de busca',
        ];
    }
}
