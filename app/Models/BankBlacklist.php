<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankBlacklist extends Model
{
    protected $fillable = [
        'nama_rekening',
        'bankname_id',
        'nomor_rekening',
        'keterangan',
    ];

    public function bankname()
    {
        return $this->belongsTo(Bankname::class);
    }
}
