<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'origin_zipcode'      => ['required', 'string', 'regex:/^\d{5}-?\d{3}$/'],
            'destination_zipcode' => ['required', 'string', 'regex:/^\d{5}-?\d{3}$/'],
            'weight_kg'           => ['required', 'numeric', 'min:0.001', 'max:30'],
            'length_cm'           => ['required', 'numeric', 'min:0.1'],
            'width_cm'            => ['required', 'numeric', 'min:0.1'],
            'height_cm'           => ['required', 'numeric', 'min:0.1'],
            'declared_value'      => ['required', 'numeric', 'min:0'],
        ];
    }
}
