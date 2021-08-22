<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

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

        if($request->filled('invitation_token')) {
            $token = $request->invitation_token;
            $data = [
                'user_name' => $request->user_name,
                'email'     => $request->email,
                'password'  => bcrypt($request->password),
            ];
        } else {
            $response['status'] = false;
            $response['message'] = 'You can not register';
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
