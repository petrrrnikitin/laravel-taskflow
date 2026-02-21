<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q'           => ['sometimes', 'string', 'max:255', 'regex:/\S/'],
            'status'      => ['sometimes', Rule::enum(TaskStatus::class)],
            'priority'    => ['sometimes', Rule::enum(TaskPriority::class)],
            'assignee_id' => ['sometimes', 'integer', 'exists:users,id'],
            'per_page'    => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}