<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'quote_id' => ['required', 'uuid'],
            'carrier'  => ['required', 'string', 'max:100'],
            'service'  => ['required', 'string', 'max:100'],
        ];
    }
}
