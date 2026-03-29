<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\SportType;

use App\Models\SportType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateSportTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<ValidationRule|string>> */
    public function rules(): array
    {
        /** @var SportType $sport_type */
        $sport_type = $this->route('sport_type');

        return [
            'name' => ['required', 'unique:sport_types,name,'.$sport_type->id, 'max:255'],
        ];
    }
}
