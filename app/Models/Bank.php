<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bank extends Model
{
    protected $guarded = [];

    public function transaction(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    public function bankname(): BelongsTo
    {
        return $this->belongsTo(Bankname::class);
    }
}
