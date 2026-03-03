<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Service;

use Illuminate\Foundation\Http\FormRequest;

final class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'unique:services,name', 'max:255'],
        ];
    }
}
