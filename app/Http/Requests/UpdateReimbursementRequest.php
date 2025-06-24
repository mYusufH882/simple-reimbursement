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
        $reimbursement = $this->route('reimbursement') ?? $this->route('id');
        $reimbursement = Reimbursement::find($reimbursement);

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
}
