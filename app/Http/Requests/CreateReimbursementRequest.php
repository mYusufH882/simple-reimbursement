<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Reimbursement;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Foundation\Http\FormRequest;

class CreateReimbursementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isEmployee();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0.01|max:99999999.99',
            'category_id' => 'required|exists:categories,id',
            'proofs' => 'required|array|min:1|max:3',
            'proofs.*' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Title is required',
            'description.required' => 'Description is required',
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount must be a valid number',
            'amount.min' => 'Amount must be greater than 0',
            'category_id.required' => 'Category is required',
            'category_id.exists' => 'Selected category does not exist',

            'proofs.required' => 'At least one proof file is required',
            'proofs.min' => 'At least one proof file is required',
            'proofs.max' => 'Maximum 3 proof files allowed',
            'proofs.*.required' => 'Proof file is required',
            'proofs.*.file' => 'Proof must be a valid file',
            'proofs.*.mimes' => 'Proof file must be PDF, JPG, or JPEG',
            'proofs.*.max' => 'Proof file size must not exceed 2MB',
        ];
    }

    public function withValidation($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->category_id) {
                $this->validateMonthlyLimit($validator);
            }
        });
    }

    private function validateMonthlyLimit($validator): void
    {
        $category = Category::find($this->category_id);
        $userId = auth()->id();

        $currentMonthQuery = Reimbursement::where('user_id', $userId)
            ->where('category_id', $this->category_id)
            ->where('status', Reimbursement::STATUS_APPROVED)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);

        if ($category->isQuotaType()) {
            $currentCount = $currentMonthQuery->count();

            if ($currentCount >= $category->limit_value) {
                $validator->errors()->add('category_id', "Quota limit bulanan ({$category->limit_value}) untuk {$category->name} telah terlampaui.");
            }
        } else {
            $currentTotal = $currentMonthQuery->sum('amount');
            $newTotal = $currentTotal + $this->amount;

            if ($newTotal > $category->limit_value) {
                $remaining = $category->limit_value - $currentTotal;
                $validator->errors()->add('amount', "Batas jumlah bulanan terlampaui. Anggaran yang tersisa: " . number_format($remaining, 2));
            }
        }
    }
}
