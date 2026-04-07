<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Group extends Model
{
    protected $guarded = [];

    public static function getActiveGroupId(): ?int
    {
        $user = auth()->user();

        // If user is restricted to a group, always return that group
        if ($user && $user->group_id > 0) {
            return $user->group_id;
        }

        // Check if there is an override in the request
        if (request()->has('group_id')) {
            return (int) request('group_id');
        }

        // Check the session for an active group (set by GroupSwitcher)
        if (session()->has('active_group_id')) {
            return (int) session('active_group_id');
        }

        // Fallback to the default group from DB
        $defaultGroup = self::where('is_default', 1)->first();
        
        return $defaultGroup ? $defaultGroup->id : 1;
    }
}

