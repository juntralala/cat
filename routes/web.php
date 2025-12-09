<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MeasurementUnitController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecipientController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\Transaction\InTransactionController;
use App\Http\Controllers\Transaction\OutTransactionController;
use App\Http\Controllers\Transaction\TransactionHistoryController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::prefix('/api')->group(function () {
        Route::get('/roles', [RoleController::class, 'getAll']);
        Route::get('/users', [UserController::class, 'getUsers']);
        Route::post('/users', [UserController::class, 'addUser']);
        Route::put('/users/{id}', [UserController::class, 'editUser']);
        Route::delete('/users/{id}', [UserController::class, 'deleteUser']);
    });
    Route::get('/recipients', [RecipientController::class, 'page'])->name('recipients');
    Route::post('/recipients', [RecipientController::class, 'store'])->name('recipients');
    Route::put('/recipients/{id}', [RecipientController::class, 'update']);
    Route::delete('/recipients/{id}', [RecipientController::class, 'destroy']);
    Route::get('/items', [ItemController::class, 'page'])->name('items');
    Route::post('/items', [ItemController::class, 'store'])->name('items');
    Route::put('/items/{id}', [ItemController::class, 'update']);
    Route::delete('/items/{id}', [ItemController::class, 'destroy']);
    Route::get('/items/inbound', [InTransactionController::class, 'index'])->name('items.inbound');
    Route::post('/items/inbound', [InTransactionController::class, 'store'])->name('items.inbound');
    Route::get('/items/outbound', [OutTransactionController::class, 'index'])->name('items.outbound');
    Route::post('/items/outbound', [OutTransactionController::class, 'store'])->name('items.outbound');
    Route::get('/items/transactions/histories', [TransactionHistoryController::class, 'index'])->name('items.transactions.history');
    Route::get('/items/transactions/histories/export/csv', [TransactionHistoryController::class, 'toCSV'])->name('items.transactions.history.export.csv');
    Route::get('/items/units', [MeasurementUnitController::class, 'page'])->name('items.units');
    Route::post('/items/units', [MeasurementUnitController::class, 'store'])->name('items.units');
    Route::put('/items/units/{id}', [MeasurementUnitController::class, 'update']);
    Route::delete('/items/units/{id}', [MeasurementUnitController::class, 'destroy']);
    Route::get('/items/stocks', [StockController::class, 'index'])->name('items.stocks');
    Route::post('/items/stocks', [StockController::class, 'store'])->name('items.stocks');
    Route::put('/items/stocks/{id}', [StockController::class, 'update']);
    Route::delete('/items/stocks/{id}', [StockController::class, 'destroy']);
    Route::get('/profile', [ProfileController::class, 'page'])->name('account.profile');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/users', [UserController::class, 'index'])->name('users');
});