<?php

use App\Http\Controllers\Api\TinodeRestAuthenticatorController;
use Illuminate\Http\Request;

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
Route::apiResource('test', 'SampleResourceApiController');
Route::namespace('Api')->group(function(){
    Route::namespace('Auth')->group(function(){
        Route::post('login', 'AuthController@login');
        Route::post('register', 'AuthController@register');
        Route::post('verify', 'AuthController@verify');
    });
    Route::resource('categories', 'ProviderCategoriesController');
    Route::prefix('providers')->group(function(){
        Route::get('getByCategoryId/{categoryId}', 'ProvidersController@getByCategoryId');
        Route::get('getByUid/{uid}', 'ProvidersController@getByUid');
    });
    Route::prefix('tinodeAuthenticator')->group(function(){
        Route::post('add','TinodeRestAuthenticatorController@add');
        Route::post('auth','TinodeRestAuthenticatorController@auth');
        Route::post('checkunique','TinodeRestAuthenticatorController@checkUnique');
        Route::post('del','TinodeRestAuthenticatorController@delete');
        Route::post('gen','TinodeRestAuthenticatorController@generate');
        Route::post('link','TinodeRestAuthenticatorController@link');
        Route::post('upd','TinodeRestAuthenticatorController@update');
        Route::post('rtagns','TinodeRestAuthenticatorController@restrictedTagNamespaces');
    });


});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    if (Gate::denies('see-user')) {
        abort(403);
    }
    return auth()->user();
});
