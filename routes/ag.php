<?php

use \Illuminate\Support\Facades\Route;


Route::group(["namespace" => "Ag", "middleware"=>["ag_locale"]], function(){
    Route::get('/index','Index@index');
    Route::post('/login','Login@login');
    Route::get('/m-login','Login@mLogin');
});

Route::group(["namespace" => "Ag", "middleware"=>["ag_login", "ag_locale"]], function(){
    Route::get('/report','Report@index');
    Route::get('/invite','User@inviteIndex');
    Route::get('/member','User@userList');
    Route::get('/betting_records','Game@bettingList');
    Route::get('/odds_table','Game@oddsTable');
    Route::post('/logout','Login@logout');
    Route::post('/add_link','User@addLink');
    Route::post('/del_link','User@delLink');

    Route::get('/m-index','Index@mIndex');
    Route::get('/m-desc','Index@mDesc');
    Route::get('/m-report','Report@mIndex');
    Route::get('/m-invite','User@mInviteIndex');
    Route::get('/m-member','User@mUserList');
    Route::get('/m-betting','Game@mBettingList');
    Route::get('/m-odds_table','Game@mOddsTable');
    Route::get('/','Index@mIndex');

});
