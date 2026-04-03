<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NetworkActivity extends Model
{
    protected $fillable = [
        'event_type',
        'site_key',
        'site_name',
        'actor',
        'status',
        'meta',
        'duration_ms',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Convenience factory — write a log entry in one call.
     *
     * Usage:
     *   NetworkActivity::log('module_toggle', 'prism', 'Prism Media Hub', [
     *       'module' => 'e_invoice', 'state' => true
     *   ], 'success', 120);
     */
    public static function log(
        string $eventType,
        string $siteKey = 'all',
        string $siteName = 'All Sites',
        array $meta = [],
        string $status = 'success',
        ?int $durationMs = null,
        string $actor = ''
    ): self {
        $actor = $actor ?: (auth()->check() ? auth()->user()->name : 'System');

        return static::create([
            'event_type' => $eventType,
            'site_key' => $siteKey,
            'site_name' => $siteName,
            'actor' => $actor,
            'status' => $status,
            'meta' => $meta,
            'duration_ms' => $durationMs,
        ]);
    }

    /**
     * Scope: newest N activities for the live feed sidebar.
     */
    public function scopeLatestFeed($query, int $limit = 5)
    {
        return $query->orderByDesc('created_at')->limit($limit);
    }

    /**
     * Human-readable one-liner for the Live Feed UI.
     */
    public function getDescriptionAttribute(): string
    {
        $meta = $this->meta ?? [];

        return match ($this->event_type) {
            'module_toggle' => sprintf(
                '%s %s on %s',
                ucwords(str_replace('_', ' ', $meta['module'] ?? 'module')),
                ($meta['state'] ?? true) ? 'enabled' : 'disabled',
                $this->site_name
            ),
            'sync_all' => "Sync-All broadcast to {$this->site_name} — " . ucfirst($this->status),
            'god_mode_login' => "God-Mode link generated for {$this->site_name}",
            'ping_check' => "Pulse: {$this->site_name} is " . ($meta['status'] ?? 'Unknown'),
            default => "Event [{$this->event_type}] on {$this->site_name}",
        };
    }

    /**
     * Returns icon + Tailwind colour classes for use in the Blade feed component.
     */
    public function getIconDataAttribute(): array
    {
        return match ($this->event_type) {
            'module_toggle' => ['icon' => 'fa-toggle-on', 'color' => 'text-emerald-400', 'bg' => 'bg-emerald-900/40 border-emerald-500/50'],
            'sync_all' => ['icon' => 'fa-rotate', 'color' => 'text-blue-400', 'bg' => 'bg-blue-900/40 border-blue-500/50'],
            'god_mode_login' => ['icon' => 'fa-bolt', 'color' => 'text-amber-400', 'bg' => 'bg-amber-900/40 border-amber-500/50'],
            'ping_check' => ['icon' => 'fa-satellite', 'color' => 'text-purple-400', 'bg' => 'bg-purple-900/40 border-purple-500/50'],
            default => ['icon' => 'fa-circle-info', 'color' => 'text-slate-400', 'bg' => 'bg-slate-800 border-slate-600'],
        };
    }

    /**
     * Site accent colour for the feed timeline dot.
     */
    public function getSiteColorAttribute(): string
    {
        return match ($this->site_key) {
            'prism' => 'text-orange-400',
            'alphafin' => 'text-sky-400',
            default => 'text-brand-red',
        };
    }
}
