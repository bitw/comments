<?php

namespace Bitw\Comments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return config('comments.only_auth')
            ? auth()->check()
            : true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $default = [
            'text' => 'required',
        ];

        $ext = [];

        if(auth()->guest() && !config('comments.only_auth')){
            $ext['name'] = 'required';
            $ext['email'] = 'required|email';
        }

        $rules = array_merge_recursive($default, $ext);

        return $rules;
    }
}