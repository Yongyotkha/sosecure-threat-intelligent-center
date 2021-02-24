<?php

namespace Modules\Reauthenticate\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // return [
        //     'code' => 'sometimes|unique:clients,code,'.$this->id,
        //     'name' => 'required',
        //     'email' => 'required|email|unique:clients,email,'.$this->id,
        //     'website' => 'sometimes|url',
        //     'facebook' => 'sometimes|url',
        //     'twitter' => 'sometimes|url',
        //     'skype' => 'sometimes|url',
        //     'linkedin' => 'sometimes|url',
        //     'contact_email' => 'sometimes|email|unique:users,email',
        //     'logo' => 'sometimes|mimes:jpeg,jpg,png|max:1000',
        // ];
        return [
            'password'         => 'required|min:6|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/',
            'password_confirm' => 'required|same:password|min:6'
        ];
    }

    public function messages()
    {
        return [
            'password.regex'         => 'Password must including UPPER/lowercase and numbers',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
