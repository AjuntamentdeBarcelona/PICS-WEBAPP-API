<?php

use Illuminate\Http\Request;
use Illuminate\Database\Seeder;
use Symfony\Component\HttpFoundation\Response;


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

$version = env('VERSION_PREFIX') . "/";

// if an end user heads to the root of the API redirect to the web app
$app->get('/', function () use ($app) {
    return redirect( env('URL_FOR_END_USERS') );
});

$app->get($version . 'pois/', 'PoisController@index');
$app->get($version . 'pois/search/', 'SearchController@index');
$app->get($version . 'pois/{id}', 'SinglePoiController@index');
$app->get($version . 'pois/{id}/body', 'SinglePoiController@index');
$app->get($version . 'categories/', 'CategoriesController@index');
$app->get($version . 'image/{id}', 'ImageController@index');
// alias for pois/search/
$app->get($version . 'search/', 'SearchController@index');
