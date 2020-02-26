<?php

use Illuminate\Support\Facades\Route;
use App\Tenant\Product;

Route::middleware(['web'])
    ->namespace('App\Http\Controllers')
    ->as('tenant.')
    ->group(function () {
        Route::get('/home', 'HomeController@index')->name('home');
        Route::get('/products', function () {
            dd(Product::all());
        });
    });
