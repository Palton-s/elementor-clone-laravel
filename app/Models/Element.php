<?php

namespace App\Models;

use Database\Factories\ElementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Element extends Model
{
    /** @use HasFactory<ElementFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'page_id',
        'parent_id',
        'uuid',
        'type',
        'name',
        'order',
        'settings',
        'content',
        'styles',
        'responsive_settings',
        'animation',
        'effects',
        'column_size',
        'css_classes',
        'css_id',
    ];

    protected $casts = [
        'uuid' => 'string',
        'settings' => 'array',
        'content' => 'array',
        'styles' => 'array',
        'responsive_settings' => 'array',
        'animation' => 'array',
        'effects' => 'array',
        'css_classes' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Element $element) {
            if (empty($element->uuid)) {
                $element->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Element::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Element::class, 'parent_id')->orderBy('order');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function isContainer(): bool
    {
        return $this->relationLoaded('children')
            ? $this->children->isNotEmpty()
            : $this->children()->exists();
    }

    /** Devices that can override desktop values, in cascade order (mobile inherits tablet). */
    public const RESPONSIVE_DEVICES = ['tablet', 'mobile'];

    /** Keys the old editor stored as "<key>_<device>" inside settings/styles. */
    public const LEGACY_RESPONSIVE_KEYS = ['padding_top', 'padding_bottom', 'padding_left', 'padding_right', 'margin_top', 'margin_bottom', 'margin_left', 'margin_right', 'font_size', 'line_height', 'letter_spacing', 'width', 'max_width', 'height', 'border_radius', 'gap'];

    /**
     * Overrides saved for one device: ['settings' => [...], 'styles' => [...]].
     */
    public function deviceOverrides(string $device): array
    {
        $saved = $this->responsive_settings[$device] ?? [];
        $overrides = [
            'settings' => is_array($saved['settings'] ?? null) ? $saved['settings'] : [],
            'styles' => is_array($saved['styles'] ?? null) ? $saved['styles'] : [],
        ];

        // Values from the old "<key>_tablet" scheme still apply, unless a new override exists
        foreach (['settings', 'styles'] as $bucket) {
            $source = $this->{$bucket} ?? [];
            foreach (self::LEGACY_RESPONSIVE_KEYS as $key) {
                $legacy = $key . '_' . $device;
                if (array_key_exists($legacy, $source) && $source[$legacy] !== '' && $source[$legacy] !== null
                    && !array_key_exists($key, $overrides[$bucket])) {
                    $overrides[$bucket][$key] = $source[$legacy];
                }
            }
        }

        return $overrides;
    }

    /**
     * Effective settings and styles for a device, cascading desktop -> tablet -> mobile.
     *
     * @return array{0: array, 1: array} [settings, styles]
     */
    public function valuesForDevice(string $device = 'desktop'): array
    {
        $settings = $this->settings ?? [];
        $styles = $this->styles ?? [];
        if ($device === 'desktop') {
            return [$settings, $styles];
        }

        foreach (self::RESPONSIVE_DEVICES as $step) {
            $overrides = $this->deviceOverrides($step);
            $settings = array_merge($settings, $overrides['settings']);
            $styles = array_merge($styles, $overrides['styles']);
            if ($step === $device) {
                break;
            }
        }

        return [$settings, $styles];
    }

    public function hasDeviceOverrides(): bool
    {
        foreach (self::RESPONSIVE_DEVICES as $device) {
            $o = $this->deviceOverrides($device);
            if ($o['settings'] !== [] || $o['styles'] !== []) {
                return true;
            }
        }

        return false;
    }

    public function getDepth(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }
}
