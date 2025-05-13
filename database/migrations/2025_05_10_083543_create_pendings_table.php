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
        Schema::create('pendings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bank_id')->constrained('banks')->cascadeOnDelete();
            $table->string('nama_rek');
            $table->integer('nominal');
            $table->integer('status')->default(1);
            $table->integer('type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendings');
    }
};
