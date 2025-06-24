<?php

namespace App\Http\Requests;

use App\Models\Reimbursement;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReimbursementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $reimbursementId = $this->route('reimbursement') ?? $this->route('id');
        $reimbursement = Reimbursement::find($reimbursementId);

        return auth()->check() &&
            auth()->user()->isEmployee() &&
            $reimbursement &&
            $reimbursement->user_id === auth()->id() &&
            $reimbursement->isPending();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'amount' => 'sometimes|numeric|min:0.01|max:99999999.99',
            'category_id' => 'sometimes|exists:categories,id',

            // Optional proof files for update
            'proofs' => 'sometimes|array|max:3',
            'proofs.*' => 'required|file|mimes:pdf,jpg,jpeg|max:2048',

            // For deleting existing proofs
            'delete_proof_ids' => 'sometimes|array',
            'delete_proof_ids.*' => 'integer|exists:proof,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.string' => 'Title must be a valid string',
            'description.string' => 'Description must be a valid string',
            'amount.numeric' => 'Amount must be a valid number',
            'amount.min' => 'Amount must be greater than 0',
            'category_id.exists' => 'Selected category does not exist',

            'proofs.max' => 'Maximum 3 proof files allowed',
            'proofs.*.file' => 'Proof must be a valid file',
            'proofs.*.mimes' => 'Proof file must be PDF, JPG, or JPEG',
            'proofs.*.max' => 'Proof file size must not exceed 2MB',

            'delete_proof_ids.*.exists' => 'Proof file does not exist',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate monthly limit if category or amount is being changed
            if ($this->category_id || $this->amount) {
                $this->validateMonthlyLimit($validator);
            }

            // Validate proof ownership if deleting proofs
            if ($this->delete_proof_ids) {
                $this->validateProofOwnership($validator);
            }
        });
    }

    private function validateMonthlyLimit($validator): void
    {
        $reimbursementId = $this->route('reimbursement') ?? $this->route('id');
        $currentReimbursement = Reimbursement::find($reimbursementId);

        $categoryId = $this->category_id ?? $currentReimbursement->category_id;
        $newAmount = $this->amount ?? $currentReimbursement->amount;

        $category = Category::find($categoryId);
        $userId = auth()->id();

        $currentMonthQuery = Reimbursement::where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->where('status', Reimbursement::STATUS_APPROVED)
            ->where('id', '!=', $reimbursementId) // Exclude current reimbursement
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month);

        if ($category->isQuotaType()) {
            $currentCount = $currentMonthQuery->count();

            if ($currentCount >= $category->limit_value) {
                $validator->errors()->add('category_id', "Quota limit bulanan ({$category->limit_value}) untuk {$category->name} telah terlampaui.");
            }
        } else {
            $currentTotal = $currentMonthQuery->sum('amount');
            $newTotal = $currentTotal + $newAmount;

            if ($newTotal > $category->limit_value) {
                $remaining = $category->limit_value - $currentTotal;
                $validator->errors()->add('amount', "Batas jumlah bulanan terlampaui. Anggaran yang tersisa: " . number_format($remaining, 2));
            }
        }
    }

    private function validateProofOwnership($validator): void
    {
        $reimbursementId = $this->route('reimbursement') ?? $this->route('id');

        foreach ($this->delete_proof_ids as $proofId) {
            $proof = \App\Models\Proof::find($proofId);
            if ($proof && $proof->reimbursement_id != $reimbursementId) {
                $validator->errors()->add('delete_proof_ids', 'You can only delete proofs from this reimbursement.');
                break;
            }
        }
    }
}
