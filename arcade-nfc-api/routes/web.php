<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CardController;
use Illuminate\Support\Facades\Route;

Route::post('/clientes', [CustomerController::class, 'store'])->name('customers.store');
Route::put('/clientes/{customer}', [CustomerController::class, 'update'])->name('customers.update');
Route::post('/cadastrar-nfc/{customer}', [CustomerController::class, 'nfcRegister'])->name('cadastro-nfc');
Route::post('/cartoes/{card}/saldo', [CardController::class, 'credit'])->name('cards.credit');
Route::patch('/cartoes/{card}/status', [CardController::class, 'updateStatus'])->name('cards.status');
