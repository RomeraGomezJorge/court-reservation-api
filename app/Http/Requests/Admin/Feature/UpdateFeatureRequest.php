<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Feature;

use App\Models\Feature;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateFeatureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<ValidationRule|string>> */
    public function rules(): array
    {
        /** @var Feature $feature */
        $feature = $this->route('feature');

        return [
            'name' => ['required', 'unique:features,name,'.$feature->id, 'max:255'],
        ];
    }
}
