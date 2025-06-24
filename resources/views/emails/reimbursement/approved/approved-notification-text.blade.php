[ADMIN] REIMBURSEMENT APPROVED NOTIFICATION

Dear {{ $admin->name }},

This is an administrative notification that a reimbursement request has been approved.

REIMBURSEMENT DETAILS:
======================
Reference Number: {{ $referenceNumber }}
Employee: {{ $employee->name }} ({{ $employee->email }})
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
APPROVAL INFORMATION:
====================
Approved by: {{ $approver->name }} ({{ $approver->role }})
Date: {{ $approvalDate ?? $reimbursement->updated_at->format('l, d F Y \a\t H:i') }}
@endif

FINANCE PROCESSING REQUIRED:
============================
This approved reimbursement needs to be processed for payment within 3-5 business days.

ADMINISTRATIVE ACTIONS REQUIRED:
================================
- Forward to finance team for payment processing
- Update employee reimbursement records
- Monitor monthly limit usage for this employee
- Track processing time and performance metrics
- Ensure proper documentation is maintained

QUICK LINKS:
============
View Full Details: {{ $appUrl }}/admin/reimbursements/{{ $reimbursement->id }}
All Reimbursements: {{ $appUrl }}/admin/reimbursements

NOTE: This is an administrative notification for tracking purposes. 
No employee action is required. Ensure finance team is notified for payment processing.

This is an automated administrative notification from the Reimbursement Management System.
For admin tracking and finance processing coordination.

Admin Dashboard: {{ $appUrl }}