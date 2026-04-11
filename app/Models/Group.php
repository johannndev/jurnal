<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Group extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($group) {
            // Jika grup ini diset sebagai default (true/1)
            if ($group->is_default) {
                // Paksa semua grup lain menjadi 0 langsung di database
                \Illuminate\Support\Facades\DB::table('groups')
                    ->where('id', '!=', $group->id)
                    ->update(['is_default' => 0]);
            }
        });
    }

    public static function getActiveGroupId(): ?int
    {
        $user = auth()->user();

        // PRIORITAS 1: Jika user sedang login dan merupakan operator terbatas (group_id > 0)
        if ($user && $user->group_id > 0) {
            return (int) $user->group_id;
        }

        // PRIORITAS 2: Jika ada parameter group_id di URL (untuk Master agar bisa ganti-ganti group)
        if (request()->has('group_id')) {
            return (int) request('group_id');
        }

        // PRIORITAS 3 (Master): Gunakan grup yang ditandai is_default di tabel groups
        $defaultGroup = self::where('is_default', 1)->first();
        if ($defaultGroup) {
            return (int) $defaultGroup->id;
        }

        return 1;
    }
}

