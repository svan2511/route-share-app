<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterPushTokenRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'token' => 'required|string|max:500',
            'device' => 'required|string|in:android,ios,web',
        ];
    }
}
