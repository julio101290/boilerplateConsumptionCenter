<?php

$routes->group('admin', function ($routes) {



    $routes->resource('consumptioncenter', [
        'filter' => 'permission:consumptioncenter-permission',
        'controller' => 'consumptioncenterController',
        'except' => 'show',
        'namespace' => 'julio101290\boilerplateconsumptioncenter\Controllers'
    ]);

    $routes->post('consumptioncenter/save'
    , 'ConsumptioncenterController::save'
    , ['namespace' => 'julio101290\boilerplateconsumptioncenter\Controllers']
        );
    
    $routes->post('consumptioncenter/getConsumptioncenter'
        , 'ConsumptioncenterController::getConsumptioncenter'
        , ['namespace' => 'julio101290\boilerplateconsumptioncenter\Controllers']
        );    

});
