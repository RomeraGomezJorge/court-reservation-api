<?php

namespace App\Http\Requests\Club;

use Illuminate\Foundation\Http\FormRequest;

final class VerifyEmailRequest extends FormRequest
{
    /** @return array<string,string> */
    public function rules(): array
    {
        return [
            'id' => 'string',
            'hash' => 'string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

}
