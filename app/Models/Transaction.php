<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Transaction extends Model
{
    protected $guarded = [];


    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    // public function member(): HasOne
    // {
    //     return $this->hasOne(Member::class);
    // }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

 
}
