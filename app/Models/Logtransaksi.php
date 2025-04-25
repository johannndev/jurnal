<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Logtransaksi extends Model
{
    protected $guarded = [];

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSingleNominalAttribute()
    {
        if($this->type == 'deposite'){
            $nominal = $this->deposite;
        }else{
            $nominal = $this->withdraw;
        }

        return $nominal;
    }

}
