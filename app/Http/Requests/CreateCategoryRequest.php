<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class CreateCategoryRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255|unique:categories,name',
            'limit_type' => 'required|in:' . Category::LIMIT_TYPE_QUOTA . ',' . Category::LIMIT_TYPE_AMOUNT,
            'limit_value' => 'required|numeric|min:0|max:99999999.99',
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
}
