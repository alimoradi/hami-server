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
        Route::post('requestVerificationCodeForPasswordRetrieval', 'AuthController@requestVerificationCodeForPasswordRetrieval');
        Route::post('retrievePassword', 'AuthController@retrievePassword');
        Route::post('postRegisterVerify', 'AuthController@postRegisterVerify');
        Route::middleware('auth:api')->group(function () {
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
        Route::get('getFees', 'ProvidersController@getFees');
        Route::get('getRandomAvatars/{categoryId?}', 'ProvidersController@getRandomAvatars');
        Route::middleware('auth:api')->group(function () {

            Route::post('addFavorite/{providerId}', 'FavoriteProvidersController@add');
            Route::post('deleteFavorite/{providerId}', 'FavoriteProvidersController@delete');
            Route::get('favorites', 'FavoriteProvidersController@index');
            Route::post('addCategory', 'ProviderCategoriesController@add');
            Route::post('editCategory', 'ProviderCategoriesController@edit');
            Route::post('uploadVerificationDocument', 'ProvidersController@uploadVerificationDocument');
            Route::get('downloadVerificationDocument/{name}', 'ProvidersController@downloadVerificationDocument');
            Route::get('getByUserId/{userId}', 'ProvidersController@getByUserId');

            Route::post('verify/{providerId}', 'ProvidersController@verifyProvider');
            Route::post('updateProviderInfo/{providerId}', 'ProvidersController@updateProviderInfo');

            Route::post('updateAboutMe/{providerId}', 'ProvidersController@updateAboutMe');

            Route::post('activitySwitchOn', 'ProvidersController@activitySwitchOn');
            Route::post('activitySwitchOff', 'ProvidersController@activitySwitchOff');

            Route::get('getActivitySwitch', 'ProvidersController@getActivitySwitch');
            Route::get('providerStatsByStatus', 'ProvidersController@providerStatsByStatus');
        });
    });
    Route::prefix('calendar')->group(function () {
        Route::middleware('auth:api')->group(function () {
            Route::post('addAvailableHours', 'AvailableHoursController@add');
            Route::post('removeAvailableHours/{availableHoursId}', 'AvailableHoursController@remove');
            Route::post('disableAvailableHours/{availableHoursId}', 'AvailableHoursController@disable');
            Route::post('enableAvailableHours/{availableHoursId}', 'AvailableHoursController@enable');
            Route::post('toggleDisabledAvailableHours/{availableHoursId}', 'AvailableHoursController@toggleDisabled');
            Route::post('availableHours', 'AvailableHoursController@get');
        });
    });
    Route::prefix('notify')->group(function () {

        Route::middleware('auth:api')->group(function () {
            Route::post('sentMessage', 'UsersController@notifySentMessage');
            Route::post('sessionUpdate', 'SessionsController@notifySessionUpdate');
        });
    });
    Route::prefix('payment')->group(function () {
        Route::get('paymentCallback', 'UsersController@paymentCallback');
        Route::middleware('auth:api')->group(function () {
            Route::get('getPaymentAuthority/{amount}', 'UsersController@getPaymentAuthority');
        });
    });
    Route::middleware('auth:api')->group(function () {
        Route::get('user', 'UsersController@me');
        Route::get('getPeers', 'UsersController@getPeers');
        Route::post('makeCall', 'UsersController@makeCall');
        Route::get('getDiscounts', 'UsersController@getDiscounts');
        Route::post('useDiscount/{discountId}', 'UsersController@useDiscount');
    });
    Route::get('stats', 'UsersController@stats');
    Route::get('config', 'UsersController@config');
    Route::prefix('users')->group(function () {
        Route::post('tempInvoiceCreate', 'UsersController@tempInvoiceCreate');
        Route::middleware('auth:api')->group(function () {
            Route::get('usersStats', 'UsersController@usersStats');
            Route::get('getById/{id}', 'UsersController@getById');
            Route::get('getByUid/{uid}', 'UsersController@getByUid');
            Route::post('updateInfo', 'UsersController@updateInfo');
            Route::get('getAdditionalInfo/{userId}', 'UsersController@getAdditionalInfo');
            Route::get('getBalance', 'UsersController@getBalance');
            Route::post('deposit', 'UsersController@deposit');
            Route::get('payments', 'UsersController@payments');
            Route::get('getAll', 'UsersController@getAll');
        });
    });
    Route::prefix('files')->group(function () {

        Route::middleware('auth:api')->group(function () {
            Route::post('uploadMessageFile', 'FileController@uploadMessageFile');
            Route::post('uploadAvatar', 'FileController@uploadAvatar');
            Route::get('downloadMessageFile/{name}', 'FileController@downloadMessageFile');
        });
    });
    Route::prefix('questions')->group(function () {
        Route::get('getAllQuestions', 'PublicQuestionAndAnswersController@getAllQuestions');
        Route::get('getAnswers/{questionId}', 'PublicQuestionAndAnswersController@getAnswers');
        Route::middleware('auth:api')->group(function () {
            Route::get('getMyQuestions', 'PublicQuestionAndAnswersController@getMyQuestions');
            Route::post('ask', 'PublicQuestionAndAnswersController@ask');
            Route::post('answer', 'PublicQuestionAndAnswersController@answer');
        });
    });
    Route::prefix('sessions')->group(function () {

        Route::middleware('auth:api')->group(function () {
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
            Route::post('updateScore/{sessionId}', 'SessionsController@updateScore');

            Route::get('getProviderPresentAndFutureSessions/{providerId}', 'SessionsController@getProviderPresentAndFutureSessions');

            Route::get('getPastSessions', 'SessionsController@getPastSessions');
            Route::get('getById/{sessionId}', 'SessionsController@getSession');

            Route::get('getSessions', 'SessionsController@getSessions');
            Route::get('getActiveRequests', 'SessionsController@getActiveRequests');
            Route::post('checkRequestEligibility', 'SessionsController@checkRequestEligibility');
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
});
