<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoadRequest extends FormRequest
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
            'vehicle_type' => ['required', 'string', 'max:255'],
            'available_space' => ['required', 'integer', 'min:1', 'max:100'],
            'departure_date' => ['required', 'date', 'after_or_equal:today'],
            'departure_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'phone' => ['required', 'string', 'max:20'],
            'route_id' => ['nullable', 'integer', 'exists:routes,id'],
            'destination_stop_id' => ['nullable', 'integer', 'exists:route_stops,id'],
        ];
    }
}
