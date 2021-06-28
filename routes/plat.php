<?php

use Illuminate\Support\Facades\Route;

Route::group(["namespace" => "Plat"], function(){
    Route::post('/bl-userinfo','BL@userinfo');
    Route::get('/bl-balance','BL@balance');
});

