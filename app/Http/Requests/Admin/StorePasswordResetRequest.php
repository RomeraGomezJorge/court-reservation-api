<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class StorePasswordResetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ajusta si necesitas control de autorización
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
        ];
    }
}
