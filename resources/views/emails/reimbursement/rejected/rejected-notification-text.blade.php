[ADMIN] REIMBURSEMENT REJECTED NOTIFICATION

Dear {{ $admin->name }},

This is an administrative notification that a reimbursement request has been rejected.

REIMBURSEMENT DETAILS:
======================
Reference Number: {{ $referenceNumber }}
Employee: {{ $employee->name }} ({{ $employee->email }})
Title: {{ $reimbursement->title }}
Category: {{ $category->name }}
Amount: {{ $formattedAmount }}
Submission Date: {{ $submissionDate }}
Rejection Date: {{ $rejectionDate }}
Status: REJECTED
@if($reimbursement->description)
Description: {{ $reimbursement->description }}
@endif

@if($reason)
REJECTION REASON:
================
{{ $reason }}
@endif

@if(isset($rejector))
REJECTION INFORMATION:
=====================
Rejected by: {{ $rejector->name }} ({{ $rejector->role }})
Date: {{ $rejectionDate }}
@endif

POLICY COMPLIANCE:
==================
Review rejection patterns to ensure consistent policy application and identify training opportunities.

ADMINISTRATIVE ACTIONS REQUIRED:
================================
- Monitor rejection patterns and policy compliance
- Review employee reimbursement history
- Track rejection reasons for training purposes
- Ensure proper communication with employee
- Update policy documentation if needed
- Monitor for potential appeals or questions

QUICK LINKS:
============
View Full Details: {{ $appUrl }}/admin/reimbursements/{{ $reimbursement->id }}
All Reimbursements: {{ $appUrl }}/admin/reimbursements

NOTE: This is an administrative notification for tracking purposes. 
Monitor employee response and provide support if needed for policy clarification.

This is an automated administrative notification from the Reimbursement Management System.
For admin tracking and policy compliance monitoring.

Admin Dashboard: {{ $appUrl }}