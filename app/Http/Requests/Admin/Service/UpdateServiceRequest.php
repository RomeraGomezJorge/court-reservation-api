<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Service;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'unique:services,name,'.$this->service->id, 'max:255'],
        ];
    }
}
