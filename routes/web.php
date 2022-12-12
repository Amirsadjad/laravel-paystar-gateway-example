<?php

use App\Http\Controllers\Front\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('orders.show');
});

Route::prefix('orders')->name('orders.')
    ->controller(OrderController::class)->group(function () {
        Route::get('/', 'show')->name('show');
        Route::get('pay', 'pay')->name('pay');
        Route::post('paystar', 'callback')->name('callback');
});
