<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\FormRequest;
use JWTAuth;

class UserProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = JWTAuth::user();
        return [
            'name'      => 'required',
            'email'     => 'nullable|email|unique:users,email,'.$user->id,
            'user_name'  => 'nullable|unique:users,user_name,'.$user->id,
        ];
    }
}
