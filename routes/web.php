<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

Livewire::setScriptRoute(function ($handle) {
    return Route::get('/public/vendor/livewire/livewire.js', $handle);
});

Livewire::setUpdateRoute(function ($handle) {
    return Route::post('/public/vendor/livewire/update', $handle);
});

Route::get('/get-bank-saldo/{id}', function ($id) {
    $bank = \App\Models\Bank::find($id);
    return response()->json([
        'saldo' => $bank?->saldo ?? 0
    ]);
});
