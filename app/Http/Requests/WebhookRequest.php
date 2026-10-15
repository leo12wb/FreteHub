<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'webhook_url'    => ['required', 'url', 'max:500'],
            'webhook_secret' => ['required', 'string', 'min:16', 'max:255'],
        ];
    }
}
