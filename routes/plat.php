<?php

use Illuminate\Support\Facades\Route;

Route::group(["namespace" => "Plat"], function(){
    Route::post('/bl-userinfo','BL@userinfo');
    Route::post('/bl-balance','BL@balance');
});

