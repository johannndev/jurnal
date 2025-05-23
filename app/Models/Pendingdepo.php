<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pendingdepo extends Model
{
    protected $table = 'pendings';

     protected $guarded = [];

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }
   
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
