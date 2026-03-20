<?php

declare(strict_types=1);

namespace App\Http\Requests\Club;

use Illuminate\Foundation\Http\FormRequest;

final class VerifyEmailRequest extends FormRequest
{
    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'id' => ['string'],
            'hash' => ['string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
