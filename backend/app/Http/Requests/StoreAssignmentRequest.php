<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'exists:subjects,id'],
            'template_id' => ['nullable', 'exists:assignment_templates,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'deadline' => ['required', 'date', 'after:now'],
            'published_at' => ['nullable', 'date'],
            'is_graded' => ['boolean'],
            'grading_type' => ['required', 'string', Rule::in(['points', 'letter', 'pass_fail'])],
            'max_score' => ['required_if:grading_type,points,letter', 'integer', 'min:1', 'max:1000'],
            'submission_type' => ['required', 'string', Rule::in(['file', 'text', 'both', 'none'])],
            'allowed_file_types' => ['nullable', 'array'],
            'max_file_size' => ['nullable', 'integer', 'min:1', 'max:102400'],
            'max_files' => ['nullable', 'integer', 'min:1', 'max:20'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_id.required' => '科目は必須です。',
            'subject_id.exists' => '指定された科目が存在しません。',
            'template_id.exists' => '指定されたテンプレートが存在しません。',
            'title.required' => '課題タイトルは必須です。',
            'deadline.required' => '提出期限は必須です。',
            'deadline.after' => '提出期限は現在より後の日時を指定してください。',
            'grading_type.required' => '採点方法は必須です。',
            'grading_type.in' => '採点方法は points、letter、pass_fail のいずれかを指定してください。',
            'max_score.required_if' => '最大スコアは必須です。',
            'submission_type.required' => '提出形式は必須です。',
            'submission_type.in' => '提出形式は file、text、both、none のいずれかを指定してください。',
        ];
    }
}
