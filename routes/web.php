<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

Route::get('/get-bank-saldo/{id}', function ($id) {
    $bank = \App\Models\Bank::find($id);
    return response()->json([
        'saldo' => $bank?->saldo ?? 0
    ]);
});
