<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'string', 'max:20'],
            'name' => ['sometimes', 'string', 'max:255'],
            'academic_year_id' => ['sometimes', 'exists:academic_years,id'],
            'term_id' => ['nullable', 'exists:terms,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'academic_year_id.exists' => 'Selected academic year does not exist.',
            'term_id.exists' => 'Selected term does not exist.',
        ];
    }
}
