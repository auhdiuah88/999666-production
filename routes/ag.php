<?php

use \Illuminate\Support\Facades\Route;


Route::group(["namespace" => "Ag"], function(){
    Route::get('/index','Index@index');
});

Route::group(["namespace" => "Ag"], function(){
    Route::get('/report','Report@index');
    Route::get('/invite','User@inviteIndex');
});
