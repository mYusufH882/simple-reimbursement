REIMBURSEMENT SUBMITTED SUCCESSFULLY

Dear {{ $employee->name }},

Great! Your reimbursement request has been submitted successfully and is now pending approval.

REIMBURSEMENT DETAILS:
======================
Reference Number: {{ $referenceNumber }}
Title: {{ $reimbursement->title }}
Category: {{ $category->name }}
Amount: {{ $formattedAmount }}
Submission Date: {{ $submissionDate }}
Status: {{ ucfirst($reimbursement->status) }}
@if($reimbursement->description)
Description: {{ $reimbursement->description }}
@endif

WHAT HAPPENS NEXT:
==================
- Your manager will review your request
- You'll receive an email notification once approved/rejected
- You can track the status in your dashboard
- Keep your reference number for future inquiries

QUICK LINKS:
============
View Details: {{ $appUrl }}/employee/reimbursements/{{ $reimbursement->id }}
My Requests: {{ $appUrl }}/employee/reimbursements

Thank you for using our reimbursement system. 
If you have any questions about your submission, please contact the HR department with your reference number.

IMPORTANT: Please save your reference number: {{ $referenceNumber }}

This is an automated confirmation email from the Reimbursement Management System.
System Dashboard: {{ $appUrl }}