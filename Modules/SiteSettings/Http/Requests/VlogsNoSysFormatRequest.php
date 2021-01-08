<?php

namespace Modules\SiteSettings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VlogsNoSysFormatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    // public function authorize()
    // {
    //     return true;
    // }

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
            'syslog' => 'required|sometimes',
            'ip_address' => 'required|sometimes',
            'protocal' => 'required|sometimes',
            'port' => 'required|sometimes',
        ];
    }

    
}
