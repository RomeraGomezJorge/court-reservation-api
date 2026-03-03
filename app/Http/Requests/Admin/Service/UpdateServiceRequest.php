<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Service;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<ValidationRule|string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'unique:services,name,'.$this->service->id, 'max:255'],
        ];
    }
}
