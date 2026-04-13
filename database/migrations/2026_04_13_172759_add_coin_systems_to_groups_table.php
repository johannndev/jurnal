<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $blueprint) {
            $blueprint->boolean('is_coin_system')->default(false)->after('is_default');
            $blueprint->boolean('is_non_coin_system')->default(false)->after('is_coin_system');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['is_coin_system', 'is_non_coin_system']);
        });
    }
};
