<?php

use Winged\Route\Route;


Route::get('./', function(){
    pre_clear_buffer_die('no param');
});

Route::get('./{$ham}', function(){
    pre_clear_buffer_die('hue dor ne mano');
});

Route::raw('./que/', function(){
    pre_clear_buffer_die('QQQQQQQQQQQQ????');
});
