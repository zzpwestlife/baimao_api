<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');

$v1 = 'App\Http\Controllers\API\V1\\';
$v2 = 'App\Http\Controllers\API\V2\\';
$v3 = 'App\Http\Controllers\API\V3\\';

$api->version(['v1', 'v2', 'v3'], function ($api) use ($v1, $v2, $v3) {
    $prefix = $v1;
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        if (strripos($_SERVER['HTTP_ACCEPT'], 'v2') !== false) {
            $prefix = $v2;
        } elseif (strripos($_SERVER['HTTP_ACCEPT'], 'v3') !== false) {
            $prefix = $v3;
        }
    }
    $api->get("/test", $prefix . 'TestController@getIndex');
    $api->get("/", $prefix . 'HomeController@getIndex');
    $api->get("/forums", $prefix . 'ForumController@getIndex');
    $api->get("/shuoshuos", $prefix . 'ShuoshuoController@getIndex');
    $api->get("/shuoshuo_comments", $prefix . 'ShuoshuoCommentController@getIndex');
    $api->get("/questions", $prefix . 'QuestionController@getIndex');
});


