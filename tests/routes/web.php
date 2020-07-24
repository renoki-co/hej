<?php

use Illuminate\Support\Facades\Route;

Route::get('/home', function () {
    return 'Home';
})->name('home');

Route::get('/register', function () {
    return 'Register';
})->name('register');

Route::group(['middleware' => [\Illuminate\Session\Middleware\StartSession::class]], function () {
    Route::get('/{provider}/redirect', 'RenokiCo\Hej\Test\Controllers\SocialController@redirect')
        ->name('redirect');

    Route::get('/{provider}/callback', 'RenokiCo\Hej\Test\Controllers\SocialController@callback')
        ->name('callback');
});
