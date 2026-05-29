<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = ['seat_id', 'booked_for', 'notes'];

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }
}
