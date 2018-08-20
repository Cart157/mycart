<?php

Route::get('/app/mall/goods/{id}',          'App\GoodsController@show');
Route::get('/app/mall/prize',               'App\PrizeController@index');
