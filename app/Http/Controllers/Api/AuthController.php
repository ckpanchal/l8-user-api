<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Invitation;
use App\Notifications\UserVerificationCode;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            $response['errors'] = $validator->errors();
            $response['status'] = false;
            return response($response, 422);
        }

        $user = User::where(['email' => request('email')])->first();

        if(!$user){
            $response['errors'] = __('passwords.user');
            $response['status'] = false;
            return response($response, 422);
        }

        if (!$token = auth('api')->attempt($request->all())) {
            return response()->json(['status' => false, 'errors' => __('auth.failed')], 401);
        }

        return $this->respondWithToken($token, $user);
    }

    /**
     * Handle logic of user registration
     * 
     * @return json
     */
    public function register(Request $request)
    {       
        $response = ['status' => true, 'data' => '', 'errors' => []];

        if($request->filled('token')) {
            $token = $request->token;
            $checkToken = Invitation::where('token',$token)->first();
            if ($checkToken) {
                $checkUserAlreadyExist = User::where('email', $checkToken->email)->first();
                if ($checkUserAlreadyExist) {
                    $response['status'] = false;
                    $response['message'] = 'User already existed with email and token.';
                    return response($response);
                }
                $data = [
                    'user_name' => $request->user_name,
                    'email'     => $checkToken->email,
                    'password'  => bcrypt($request->password),
                    'registered_at' => Carbon::now()
                ];
                $user = User::create($data);
                if ($user) {
                    $verificationCode = mt_rand(000001, 999999);
                    $user->verification_code = $verificationCode;
                    $user->save();

                    $user->notify(new UserVerificationCode($verificationCode));
                    $response['message'] = 'Verification code sent to your email. Please verify you email.';

                    if (!$token = auth('api')->attempt(['email' => $checkToken->email,'password' => $request->password])) {
                        return response()->json(['status' => false, 'errors' => __('auth.failed')], 401);
                    }

                    return $this->respondWithToken($token, $user);
                } else {
                    $response['status'] = false;
                    $response['message'] = 'Oops something went wrong. User not registered successfully.';
                }
            } else {
                $response['status'] = false;
                $response['message'] = 'Invitation token invalid.';
            }
        }

        return response($response);
    }

    /**
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $user=null)
    {
        return response()->json([
            'accessToken'           => $token,
            'tokenType'             => 'bearer',
            'userId'                => $user->id,
            'expiresIn'             => auth('api')->factory()->getTTL() * 60,
        ]);
    }
}
