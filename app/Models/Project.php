<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'settings',
        'is_default',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_default' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (!$project->slug) {
                $project->slug = Str::slug($project->name) ?: 'project';
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    public function themeTemplates(): HasMany
    {
        return $this->hasMany(ThemeTemplate::class);
    }


    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
