<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest as Request;

class UserRequest extends Request
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
          'name' => 'required|max:255',
          'email' => 'required|email|max:255|unique:users',
          'username' => 'required|max:255|unique:users',
          'password' => 'required|confirmed|min:6',
        ];
    }

    public function messages()
    {
      return [
        'password.required' => 'user_error_password_required',
        'password.confirmed' => 'user_error_password_confirmed',
        'password.min' => 'user_error_password_min',
        'email.unique' => 'user_error_email_unique',
        'username.unique' => 'user_error_username_unique',
      ];
    }
}
