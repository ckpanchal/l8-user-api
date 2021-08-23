<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\FormRequest;

class RegistrationRequest extends FormRequest
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
        return [
            'user_name' => 'required|unique:users|string|min:10|max:12',
            'password'  => 'required|confirmed|min:8|max:15'
        ];
    }
}
