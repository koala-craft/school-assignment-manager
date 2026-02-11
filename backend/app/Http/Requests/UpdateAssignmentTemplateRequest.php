<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssignmentTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'grading_type' => ['sometimes', 'string', Rule::in(['points', 'letter', 'pass_fail'])],
            'max_score' => ['sometimes', 'integer', 'min:1', 'max:1000'],
            'submission_type' => ['sometimes', 'string', Rule::in(['file', 'text', 'both', 'none'])],
            'allowed_file_types' => ['nullable', 'array'],
            'max_file_size' => ['nullable', 'integer', 'min:1', 'max:102400'],
            'max_files' => ['nullable', 'integer', 'min:1', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'grading_type.in' => '採点方法は points、letter、pass_fail のいずれかを指定してください。',
            'submission_type.in' => '提出形式は file、text、both、none のいずれかを指定してください。',
        ];
    }
}
