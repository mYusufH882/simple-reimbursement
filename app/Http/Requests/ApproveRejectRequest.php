<?php

namespace App\Http\Requests;

use App\Models\Reimbursement;
use Illuminate\Foundation\Http\FormRequest;

class ApproveRejectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $reimbursement = $this->route('reimbursement') ?? $this->route('id');
        $reimbursement = Reimbursement::find($reimbursement);

        return auth()->check() &&
            auth()->user()->isManager() &&
            $reimbursement &&
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
            'action' => 'required|in:approve,reject',
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Action is required',
            'action.in' => 'Action must be either approve or reject',
        ];
    }
}
