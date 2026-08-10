<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| All non-API routes serve the React SPA index.html.
| React Router handles client-side routing from here.
*/

Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!api).*$');
