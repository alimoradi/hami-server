<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Route;
use App\Interfaces\AccountVerifier;
use App\Interfaces\UserAccessManager;
use App\Libraries\Notifications\MessageReceived;
use App\Provider;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{

    protected $accountVerifier;
    protected $accessManager;

    public function __construct(AccountVerifier $verifier, UserAccessManager $accessManager)
    {
        $this->accountVerifier = $verifier;
        $this->accessManager = $accessManager;
    }
    public function updateChatCredentials(Request $request)
    {
        $request->validate([
            'tinode_pass' => 'required', 'tinode_uid' => 'required',
            'tinode_username' => 'required'
        ]);
        $user = auth()->user();
        $user->tinode_pass = $request->input('tinode_pass');
        $user->tinode_uid = $request->input('tinode_uid');
        $user->tinode_username = $request->input('tinode_username');
        $user->save();
        return response()->json(['success' => true]);
    }
    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required', 'new_password' => 'required'
        ]);

        if (auth()->user()->password != $request->input('old_password')) {
            abort(401, 'Wrong Password');
        }
        auth()->user()->password = $request->input('new_password');
        auth()->user()->save();
        return response()->json(['success' => true]);
    }
    public function register(AccountVerifier $verifier, Request $request)
    {
        $validations = [
            'phone' => 'required|unique:users',
            'password' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'role_id' => 'required'
        ];
        $roleId = $request->input('role_id');
        $isProvider = $roleId == $this->accessManager->getRoleId('service_provider');
        if ($isProvider) {
           // $validations['provider_category_id'] = 'required';
        }
        $request->validate($validations);
        $user = new User;
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->phone = $request->input('phone');
        $user->password = $request->input('password');

        $user->role_id = $roleId;
        //$user->tinode_username = $request->input('tinode_username');
        //$user->tinode_pass = $request->input('tinode_pass');
        //$user->tinode_uid = $request->input('tinode_uid');
        //$user->role_id = $this->accessManager->getRoleId('service_user');  
        if ($user->save() && $isProvider) {
            $provider = new Provider;
            //$provider->provider_category_id = $request->input('provider_category_id');
            //$provider->per_minute_text_fee = 1200;
            $user->provider()->save($provider);
        }


        return response()->json(['success' => true]);
    }
    public function requestVerificationCode(AccountVerifier $verifier, Request $request)
    {
        $request->validate(['phone' => 'required'], ['password' => 'required']);
        $user = null;
        try {
            $user = User::where('phone', $request->input('phone'))
                ->where('password', $request->input('password'))
                ->firstOrFail();
        } catch (Exception $e) {
            abort(404, 'phone number and password mismatch');
        }
        $user->verification_code = $this->accountVerifier->generateVerificationCode();
        if ($this->accountVerifier->sendVerificationCode($user->verification_code, $user->phone)) {
            $user->phone_verified_at = null;
            $user->save();
            return response()->json(['success' => true], 200);
        }
        abort(400, 'verification code not sent');
    }
    public function verify(Request $request)
    {

        $request->validate(['phone' => 'required', 'verification_code' => 'required']);
        $user = User::where('phone', $request->input('phone'))->firstOrFail();
        if ($user->verification_code == $request->input('verification_code')) {
            $user->phone_verified_at = Carbon::now();
            $user->save();
            return response()->json(['success' => true]);
        }
        return response()->json([
            'error' => 'verification_code_mismatch', 'error_message' => 'The provided verification code is invalid.',
            'error_description' => 'The provided verification code is invalid.'
        ], 404);
    }
    public function setFcmToken(Request $request)
    {
        $request->validate(['fcm_token' => 'required']);
        auth()->user()->fcm_token = $request->input('fcm_token');
        auth()->user()->save();
        return response()->json(['success' => true]);
    }
    public function unsetFcmToken()
    {

        auth()->user()->fcm_token = null;
        auth()->user()->save();
        return response()->json(['success' => true]);
    }
    public function login(Request $request)
    {


        $credentials = $request->only('username', 'password');
        
        if (auth()->attempt(['phone' => $credentials['username'], 'password' => $credentials['password']])) {

            $user = User::where('phone', $credentials['username'])->first();
            if($user->phone_verified_at == null)
            {
                return  response()->json([
                    'error' => 'not_verified', 'error_message' => 'user is not verified',
                    'error_description' => 'User is not verified.'
                ], 403);
            }
            $role = $user->checkRole();
            $scopes = '';
            // grant scopes based on the role that we get previously
            if ($role == 'service_user') {
                $scopes = config('scopes.service_user');
            } else if ($role == 'service_provider') {
                $scopes = config('scopes.service_provider');
            } else if ($role == 'admin') {
                $scopes = config('scopes.admin');
            }
            $request->request->add([
                'scope' => implode(' ', $scopes)
            ]);
            // forward the request to the oauth token request endpoint
            $tokenRequest = Request::create(
                '/oauth/token',
                'post'
            );
            //var_dump($request->all());die;
            return Route::dispatch($tokenRequest);
        }
        
        return response()->json([
            'error' => 'invalid_credentials', 'error_message' => 'The provided credentials are invalid.',
            'error_description' => 'The provided credentials are invalid.'
        ], 403);


        // implement your user role retrieval logic, for example retrieve from `roles` database table

    }
}
