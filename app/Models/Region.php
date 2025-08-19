<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'level',
        'parent_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'level' => 'integer',
    ];

    /**
     * Get the parent region.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'parent_code', 'code');
    }

    /**
     * Get the child regions.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Region::class, 'parent_code', 'code');
    }

    /**
     * Scope to filter by level.
     */
    public function scopeByLevel($query, int $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope to get provinces (level 1).
     */
    public function scopeProvinces($query)
    {
        return $query->where('level', 1);
    }

    /**
     * Scope to get regencies/cities (level 2).
     */
    public function scopeRegencies($query)
    {
        return $query->where('level', 2);
    }

    /**
     * Scope to get districts (level 3).
     */
    public function scopeDistricts($query)
    {
        return $query->where('level', 3);
    }

    /**
     * Scope to get villages (level 4).
     */
    public function scopeVillages($query)
    {
        return $query->where('level', 4);
    }

    /**
     * Get the full hierarchical path of the region.
     */
    public function getFullPathAttribute(): string
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }

    /**
     * Get level name in English.
     */
    public function getLevelNameAttribute(): string
    {
        return match($this->level) {
            1 => 'Province',
            2 => 'Regency/City',
            3 => 'District',
            4 => 'Village',
            default => 'Unknown',
        };
    }
}
