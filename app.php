<?php

use Winged\Route\Route;

Route::raw('{$hue?}', function(){

    \Winged\App\App::virtualizeUri('./');

});