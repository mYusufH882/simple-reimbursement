REIMBURSEMENT APPROVED!

Dear {{ $employee->name }},

Congratulations! Your reimbursement request has been APPROVED and will be processed for payment.

REIMBURSEMENT DETAILS:
======================
Reference Number: {{ $referenceNumber }}
Title: {{ $reimbursement->title }}
Category: {{ $category->name }}
Approved Amount: {{ $formattedAmount }}
Submission Date: {{ $submissionDate }}
Approval Date: {{ $approvalDate ?? $reimbursement->updated_at->format('d F Y, H:i') }}
Status: APPROVED
@if($reimbursement->description)
Description: {{ $reimbursement->description }}
@endif

@if(isset($approver))
Approved by: {{ $approver->name ?? 'Manager' }}
Date: {{ $approvalDate ?? $reimbursement->updated_at->format('l, d F Y \a\t H:i') }}
@endif

WHAT HAPPENS NEXT:
==================
- Your request will be forwarded to the finance team
- Payment processing typically takes 3-5 business days
- You'll receive payment via your registered bank account
- Keep this reference number for your records

QUICK LINKS:
============
View Details: {{ $appUrl }}/employee/reimbursements/{{ $reimbursement->id }}
My Requests: {{ $appUrl }}/employee/reimbursements

IMPORTANT: Please allow 3-5 business days for payment processing. 
If you have any questions about the payment schedule, please contact the finance department.

Congratulations on your approved reimbursement!

This is an automated email from the Reimbursement Management System.
System Dashboard: {{ $appUrl }}