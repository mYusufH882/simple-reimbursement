REIMBURSEMENT REJECTED

Dear {{ $employee->name }},

We regret to inform you that your reimbursement request has been REJECTED. 
Please review the details and reason below.

REIMBURSEMENT DETAILS:
======================
Reference Number: {{ $referenceNumber }}
Title: {{ $reimbursement->title }}
Category: {{ $category->name }}
Amount: {{ $formattedAmount }}
Submission Date: {{ $submissionDate }}
Rejection Date: {{ $rejectionDate ?? $reimbursement->updated_at->format('d F Y, H:i') }}
Status: REJECTED
@if($reimbursement->description)
Description: {{ $reimbursement->description }}
@endif

@if($reason)
REASON FOR REJECTION:
====================
{{ $reason }}
@endif

@if(isset($rejector))
Rejected by: {{ $rejector->name ?? 'Manager' }}
Date: {{ $rejectionDate ?? $reimbursement->updated_at->format('l, d F Y \a\t H:i') }}
@endif

WHAT YOU CAN DO NEXT:
=====================
- Review the rejection reason carefully
- Gather additional documentation if needed
- Contact your manager for clarification
- Submit a new request with corrections
- Reach out to HR if you need assistance

QUICK LINKS:
============
Submit New Request: {{ $appUrl }}/employee/reimbursements/create
View Details: {{ $appUrl }}/employee/reimbursements/{{ $reimbursement->id }}

NEED HELP? 
If you believe this rejection was made in error or need clarification on the company's 
reimbursement policy, please don't hesitate to contact your manager or the HR department.

We're here to help you get your expenses reimbursed properly.

This is an automated email from the Reimbursement Management System.
System Dashboard: {{ $appUrl }}