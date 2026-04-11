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
        Schema::create('bank_blacklists', function (Blueprint $table) {
            $table->id();
            $table->string('nama_rekening');
            $table->foreignId('bankname_id')->constrained('banknames');
            $table->string('nomor_rekening');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_blacklists');
    }
};
