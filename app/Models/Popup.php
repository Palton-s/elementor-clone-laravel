<?php

namespace App\Models;

use Database\Factories\PopupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Popup extends Model
{
    /** @use HasFactory<PopupFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'project_id',
        'page_id',
        'title',
        'slug',
        'type',
        'status',
        'triggers',
        'settings',
        'conditions',
        'order',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'triggers' => 'array',
            'settings' => 'array',
            'conditions' => 'array',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Popup $popup) {
            if (!$popup->slug) {
                $popup->slug = Str::slug($popup->title) . '-' . Str::random(6);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function page(): BelongsTo
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

    public static function types(): array
    {
        return ['popup', 'modal'];
    }

    public static function triggerTypes(): array
    {
        return [
            'on_load' => ['label' => 'Ao carregar a página', 'has_value' => false],
            'on_timer' => ['label' => 'Após um tempo (segundos)', 'has_value' => true, 'placeholder' => 'Segundos (padrão 3)'],
            'on_scroll' => ['label' => 'Ao rolar (%)', 'has_value' => true, 'placeholder' => 'Porcentagem da página'],
            'on_exit' => ['label' => 'Intenção de saída', 'has_value' => false],
            'on_click' => ['label' => 'Ao clicar (seletor CSS)', 'has_value' => true, 'placeholder' => 'Seletor CSS, ex.: .meu-botao'],
        ];
    }
}
