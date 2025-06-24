NEW REIMBURSEMENT SUBMISSION

Dear {{ $manager->name }},

A new reimbursement request has been submitted and is waiting for your review.

REIMBURSEMENT DETAILS:
======================
Reference Number: {{ 'REF-' . str_pad($reimbursement->id, 6, '0', STR_PAD_LEFT) }}
Employee: {{ $employee->name }}
Title: {{ $reimbursement->title }}
Category: {{ $category->name }}
Amount: {{ $formattedAmount }}
Submission Date: {{ $reimbursement->submitted_at->format('d F Y, H:i') }}
Status: {{ ucfirst($reimbursement->status) }}
@if($reimbursement->description)
Description: {{ $reimbursement->description }}
@endif

ACTIONS REQUIRED:
================
Please review this request at your earliest convenience.
You can approve or reject the request through the system.

View Details: {{ $appUrl }}/manager/reimbursements/{{ $reimbursement->id }}
All Requests: {{ $appUrl }}/manager/reimbursements

This is an automated email from the Reimbursement Management System.
If you have any questions, please contact the HR department.

System Dashboard: {{ $appUrl }}