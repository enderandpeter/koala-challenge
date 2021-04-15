<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return json_decode(file_get_contents(storage_path('tests/menu-koala-json-response.json')), true);
});

$router->get('koala/locations/{id}[/{dataType}]', [
    'as' => 'get-restaurant-data',
    'uses' => 'Restaurants\DataController@getData'
]);
