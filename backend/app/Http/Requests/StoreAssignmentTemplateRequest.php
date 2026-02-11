<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssignmentTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'grading_type' => ['required', 'string', Rule::in(['points', 'letter', 'pass_fail'])],
            'max_score' => ['required_if:grading_type,points,letter', 'integer', 'min:1', 'max:1000'],
            'submission_type' => ['required', 'string', Rule::in(['file', 'text', 'both', 'none'])],
            'allowed_file_types' => ['nullable', 'array'],
            'max_file_size' => ['nullable', 'integer', 'min:1', 'max:102400'],
            'max_files' => ['nullable', 'integer', 'min:1', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'テンプレート名は必須です。',
            'title.required' => '課題タイトルは必須です。',
            'grading_type.required' => '採点方法は必須です。',
            'grading_type.in' => '採点方法は points、letter、pass_fail のいずれかを指定してください。',
            'max_score.required_if' => '最大スコアは必須です。',
            'submission_type.required' => '提出形式は必須です。',
            'submission_type.in' => '提出形式は file、text、both、none のいずれかを指定してください。',
        ];
    }
}
