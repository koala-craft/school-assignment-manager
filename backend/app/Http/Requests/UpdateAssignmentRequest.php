<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['sometimes', 'exists:subjects,id'],
            'template_id' => ['nullable', 'exists:assignment_templates,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'deadline' => ['sometimes', 'date'],
            'published_at' => ['nullable', 'date'],
            'is_graded' => ['boolean'],
            'grading_type' => ['sometimes', 'string', Rule::in(['points', 'letter', 'pass_fail'])],
            'max_score' => ['sometimes', 'integer', 'min:1', 'max:1000'],
            'submission_type' => ['sometimes', 'string', Rule::in(['file', 'text', 'both', 'none'])],
            'allowed_file_types' => ['nullable', 'array'],
            'max_file_size' => ['nullable', 'integer', 'min:1', 'max:102400'],
            'max_files' => ['nullable', 'integer', 'min:1', 'max:20'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_id.exists' => '指定された科目が存在しません。',
            'template_id.exists' => '指定されたテンプレートが存在しません。',
            'grading_type.in' => '採点方法は points、letter、pass_fail のいずれかを指定してください。',
            'submission_type.in' => '提出形式は file、text、both、none のいずれかを指定してください。',
        ];
    }
}
