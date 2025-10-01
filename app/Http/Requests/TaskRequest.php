<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'nullable|in:pending,in_progress,completed',
            'order' => 'nullable|integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'The title is required.',
            'title.max' => 'The title cannot exceed 255 characters.',
            'description.max' => 'The description cannot exceed 1000 characters.',
            'status.in' => 'The status must be: pending, in progress or completed.',
            'order.integer' => 'The order must be an integer.',
            'order.min' => 'The order must be greater than or equal to 0.',
        ];
    }
}
