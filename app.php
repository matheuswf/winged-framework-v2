<?php

use Winged\Route\Route;


Route::get('./', function(){
    pre_clear_buffer_die('no param');
});

Route::get('./{$ham}', function($ham){
    return [
        'status' => $ham
    ];
})->where('ham', '[a-z]');

Route::raw('./que/', function(){
    pre_clear_buffer_die('QQQQQQQQQQQQ????');
});
