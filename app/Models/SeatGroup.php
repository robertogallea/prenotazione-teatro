<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeatGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'section',
        'block_key',
        'label',
        'sort_order',
        'rows',
        'seats_per_row',
        'seat_offset',
        'has_corridor',
        'corridor_after_slot',
    ];

    protected $casts = [
        'rows' => 'array',
        'seats_per_row' => 'array',
        'has_corridor' => 'boolean',
    ];

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class, 'block_key', 'block_key');
    }
}
