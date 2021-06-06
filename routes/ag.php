<?php

use \Illuminate\Support\Facades\Route;


Route::group(["namespace" => "Ag", "middleware"=>["ag_locale"]], function(){
    Route::get('/index','Index@index');
    Route::post('/login','Login@login');
});

Route::group(["namespace" => "Ag", "middleware"=>["ag_login", "ag_locale"]], function(){
    Route::get('/report','Report@index');
    Route::get('/invite','User@inviteIndex');
    Route::post('/logout','Login@logout');
    Route::post('/add_link','User@addLink');
    Route::post('/del_link','User@delLink');
});
