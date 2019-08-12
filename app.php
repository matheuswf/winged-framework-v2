<?php

use Winged\Route\Route;


Route::get('./', function () {
    /**
     * @var $this Route
     */
    $this->response()->forceHtml();
    return 'ok';
});

Route::get('./{$ham}', function ($ham) {
    /**
     * @var $this Route
     */
    $this->response()->forceJson();
    return [
        'status' => $ham
    ];
})->where('ham', '[a-z]');

Route::post('./queijo/', function () {
    return ['status' => true];
});

Route::raw('./que/', function () {

});

Route::notFound(function(){
    /**
     * @var $this Route
     */
    return 'deu ruim :(';
});