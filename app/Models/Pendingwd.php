<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pendingwd extends Model
{
    protected $guarded = [];
    
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

      public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }
}
