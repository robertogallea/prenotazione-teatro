<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Seat extends Model
{
    use HasFactory;

    protected $fillable = ['section', 'block_key', 'row', 'number', 'label'];

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(SeatGroup::class, 'block_key', 'block_key');
    }
}
