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


Route::namespace('Api')->group(function () {
    Route::namespace('Auth')->group(function () {
        Route::post('login', 'AuthController@login');
        Route::post('register', 'AuthController@register');
        Route::post('verify', 'AuthController@verify');
        Route::post('requestVerificationCode', 'AuthController@requestVerificationCode');
        Route::post('postRegisterVerify', 'AuthController@postRegisterVerify');
        Route::middleware('auth:api')->group(function(){
            Route::post('updateChatCredentials', 'AuthController@updateChatCredentials');
            Route::post('setFcmToken', 'AuthController@setFcmToken');
            Route::post('unsetFcmToken', 'AuthController@unsetFcmToken');
            Route::post('changePassword', 'AuthController@changePassword');
        });
    });
    Route::resource('categories', 'ProviderCategoriesController');
    Route::prefix('providers')->group(function () {
        Route::get('getByCategoryId/{categoryId}', 'ProvidersController@getByCategoryId');
        Route::get('getAll', 'ProvidersController@getAll');
        Route::get('getByUid/{uid}', 'ProvidersController@getByUid');
        Route::get('getById/{id}', 'ProvidersController@getById');
        Route::middleware('auth:api')->group(function(){
            Route::post('addFavorite/{providerId}', 'FavoriteProvidersController@add');
            Route::post('deleteFavorite/{providerId}', 'FavoriteProvidersController@delete');
            Route::get('favorites', 'FavoriteProvidersController@index');
            Route::post('addCategory', 'ProviderCategoriesController@add');
            Route::post('editCategory', 'ProviderCategoriesController@edit');
            Route::post('uploadVerificationDocument', 'ProvidersController@uploadVerificationDocument');
            Route::get('getByUserId/{userId}', 'ProvidersController@getByUserId');

            Route::post('verify/{providerId}', 'ProvidersController@verifyProvider');
        });
    });
    Route::prefix('calendar')->group(function () {
       Route::middleware('auth:api')->group(function(){
            Route::post('addAvailableHours', 'AvailableHoursController@add');
            Route::post('removeAvailableHours/{availableHoursId}', 'AvailableHoursController@remove');
            Route::post('disableAvailableHours/{availableHoursId}', 'AvailableHoursController@disable');
            Route::post('enableAvailableHours/{availableHoursId}', 'AvailableHoursController@enable');
            Route::post('toggleDisabledAvailableHours/{availableHoursId}', 'AvailableHoursController@toggleDisabled');
            Route::post('availableHours', 'AvailableHoursController@get');
        });
    });
    Route::prefix('notify')->group(function () {
        
        Route::middleware('auth:api')->group(function(){
            Route::post('sentMessage', 'UsersController@notifySentMessage');
            Route::post('sessionUpdate', 'SessionsController@notifySessionUpdate');
        });
    });
    Route::prefix('users')->group(function () {
        
        Route::middleware('auth:api')->group(function(){
            Route::get('getById/{id}', 'UsersController@getById');
            Route::get('getByUid/{uid}', 'UsersController@getByUid');
            Route::post('updateInfo', 'UsersController@updateInfo');
            Route::get('getAdditionalInfo/{userId}', 'UsersController@getAdditionalInfo');

        });
    });
    Route::prefix('files')->group(function () {
        
        Route::middleware('auth:api')->group(function(){
            Route::post('uploadMessageFile', 'FileController@uploadMessageFile');
            Route::post('uploadAvatar', 'FileController@uploadAvatar');
            Route::get('downloadMessageFile/{name}', 'FileController@downloadMessageFile');
        });
    });
    Route::prefix('sessions')->group(function () {
        
        Route::middleware('auth:api')->group(function(){
            Route::post('request', 'SessionsController@request');
            Route::post('start/{sessionId}', 'SessionsController@start');
            Route::post('accept/{sessionId}', 'SessionsController@accept');
            Route::post('end/{sessionId}', 'SessionsController@end');
            Route::post('selectRangeByDate', 'SessionsController@selectRangeByDate');
            Route::get('userActiveSessions', 'SessionsController@userActiveSessions');
            Route::get('userEndedSessions', 'SessionsController@userEndedSessions');
            Route::get('userRequestedSessions', 'SessionsController@userRequestedSessions');
            Route::get('providerActiveSessions', 'SessionsController@providerActiveSessions');
            Route::get('providerEndedSessions', 'SessionsController@providerEndedSessions');
            Route::get('providerRequestedSessions', 'SessionsController@providerRequestedSessions');
            Route::get('getPresentAndFutureSessions', 'SessionsController@getPresentAndFutureSessions');

            Route::get('getPastSessions', 'SessionsController@getPastSessions');

        });
    });
    Route::prefix('tinodeAuthenticator')->group(function () {
        Route::post('add', 'TinodeRestAuthenticatorController@add');
        Route::post('auth', 'TinodeRestAuthenticatorController@auth');
        Route::post('checkunique', 'TinodeRestAuthenticatorController@checkUnique');
        Route::post('del', 'TinodeRestAuthenticatorController@delete');
        Route::post('gen', 'TinodeRestAuthenticatorController@generate');
        Route::post('link', 'TinodeRestAuthenticatorController@link');
        Route::post('upd', 'TinodeRestAuthenticatorController@update');
        Route::post('rtagns', 'TinodeRestAuthenticatorController@restrictedTagNamespaces');
    });




    Route::middleware('auth:api')->get('user', function (Request $request) {
        if (Gate::denies('see-user')) {
            abort(403);
        }
        return auth()->user();
    });
});
