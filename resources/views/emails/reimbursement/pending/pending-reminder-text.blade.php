PENDING REIMBURSEMENTS REMINDER

Dear {{ $manager->name }},

You have {{ $pendingCount }} reimbursement {{ $pendingCount === 1 ? 'request' : 'requests' }} waiting for your review and approval.

SUMMARY STATISTICS:
==================
Total Pending: {{ $pendingCount }}
Urgent (3+ days): {{ $urgentCount }}
Total Amount: {{ $totalPendingAmount }}
Oldest Request: {{ $oldestPending ? $oldestPending['days_pending'] : 0 }} days

@if($oldestPending && $oldestPending['days_pending'] > 3)
URGENT ALERT:
============
The oldest pending request is {{ $oldestPending['days_pending'] }} days old.
Please prioritize "{{ $oldestPending['title'] }}" from {{ $oldestPending['employee_name'] ?? 'Unknown Employee' }}.
@endif

PENDING REQUESTS LIST:
=====================
@foreach($pendingReimbursements as $reimbursement)
@php
    $daysPending = \Carbon\Carbon::parse($reimbursement['submitted_at'])->diffInDays(now());
    $isUrgent = $daysPending >= 3;
@endphp

{{ $isUrgent ? '[URGENT] ' : '' }}{{ $reimbursement['title'] }}
Employee: {{ $reimbursement['employee_name'] ?? 'Unknown' }}
Amount: {{ 'IDR ' . number_format($reimbursement['amount'], 0, ',', '.') }}
Days Pending: {{ $daysPending }} {{ $daysPending === 1 ? 'day' : 'days' }}
{{ $isUrgent ? '*** REQUIRES IMMEDIATE ATTENTION ***' : '' }}

@endforeach

ACTION ITEMS:
============
- Review and approve/reject pending requests promptly
- Prioritize requests that are 3+ days old
- Check supporting documents before making decisions
- Provide clear reasons for any rejections
- Contact employees if additional information is needed

QUICK LINKS:
============
Review All Pending: {{ $appUrl }}/manager/reimbursements?status=pending
Manager Dashboard: {{ $appUrl }}/manager/dashboard

IMPORTANT: Timely review of reimbursement requests helps maintain employee satisfaction 
and ensures smooth expense processing. Please try to review requests within 2-3 business days.

This is an automated reminder from the Reimbursement Management System.
Sent to help you stay on top of pending approvals.

Manager Dashboard: {{ $appUrl }}

---