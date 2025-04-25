<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;


class Member extends Model
{
    protected $guarded = [];

    public function transaction(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    public function log(): HasOne
    {
        return $this->hasOne(Logtransaksi::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
