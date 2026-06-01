<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_city' => ['required', 'string', 'max:255'],
            'to_city' => ['required', 'string', 'max:255', 'different:from_city'],
            'exclude_load_id' => ['sometimes', 'integer', 'exists:loads,id'],
        ];
    }
}
