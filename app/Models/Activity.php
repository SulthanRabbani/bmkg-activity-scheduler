<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'region_code',
        'preferred_date',
        'selected_time_slots',
        'weather_data',
        'status',
        'notes'
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'selected_time_slots' => 'array',
        'weather_data' => 'array',
    ];

    /**
     * Get the region associated with this activity
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_code', 'code');
    }

    /**
     * Scope for activities by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for activities by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('preferred_date', [$startDate, $endDate]);
    }
}
