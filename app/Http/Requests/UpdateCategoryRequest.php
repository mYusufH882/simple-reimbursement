<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categoryId = $this->route('id') ?? $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($categoryId),
            ],
            'limit_type' => 'required|in:' . Category::LIMIT_TYPE_QUOTA . ',' . Category::LIMIT_TYPE_AMOUNT,
            'limit_value' => 'required|numeric|min:0|max:999999999.99',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required',
            'name.unique' => 'Category name already exists',
            'limit_type.required' => 'Limit type is required',
            'limit_type.in' => 'Limit type must be either quota or amount',
            'limit_value.required' => 'Limit value is required',
            'limit_value.numeric' => 'Limit value must be a number',
            'limit_value.min' => 'Limit value must be at least 0',
            'limit_value.max' => 'Limit value is too large',
        ];
    }

    public function attributes(): array
    {
        return [
            'limit_type' => 'limit type',
            'limit_value' => 'limit value',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $categoryId = $this->route('id') ?? $this->route('category');
            $category = Category::find($categoryId);

            if ($category && $category->reimbursements()->count() > 0) {
                // If category has reimbursements, only allow changing limit_value
                if ($this->limit_type !== $category->limit_type) {
                    $validator->errors()->add('limit_type', 'Tidak dapat mengubah jenis batas saat kategori memiliki penggantian yang ada');
                }
            }
        });
    }
}
