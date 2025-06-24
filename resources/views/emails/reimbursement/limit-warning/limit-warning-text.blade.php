REIMBURSEMENT LIMIT WARNING

Dear {{ $employee->name }},

This is a friendly reminder that you're approaching your monthly reimbursement limit. 
Please review your current usage below.

MONTHLY LIMIT STATISTICS:
=========================
Monthly Limit: {{ 'IDR ' . number_format($monthlyLimit ?? 5000000, 0, ',', '.') }}
Used This Month: {{ 'IDR ' . number_format($usedAmount ?? 0, 0, ',', '.') }}
Remaining: {{ 'IDR ' . number_format(($monthlyLimit ?? 5000000) - ($usedAmount ?? 0), 0, ',', '.') }}
Usage Percentage: {{ round((($usedAmount ?? 0) / ($monthlyLimit ?? 5000000)) * 100, 1) }}%

THIS MONTH'S ACTIVITY:
======================
Current Month: {{ now()->format('F Y') }}
Approved Requests: {{ $approvedCount ?? 0 }} requests
Pending Requests: {{ $pendingCount ?? 0 }} requests
Days Remaining: {{ now()->daysInMonth - now()->day }} days

TIPS FOR MANAGING YOUR REIMBURSEMENT LIMIT:
===========================================
- Plan your expenses carefully for the rest of the month
- Prioritize essential business expenses
- Consider if any expenses can be postponed to next month
- Check with your manager about limit adjustments if needed
- Keep track of your submissions in the dashboard

QUICK LINKS:
============
View My Requests: {{ $appUrl }}/employee/reimbursements
Go to Dashboard: {{ $appUrl }}/employee/dashboard

IMPORTANT: This limit resets on the 1st of each month. 
If you need to exceed your limit for essential business expenses, 
please contact your manager or HR department.

This is an automated reminder from the Reimbursement Management System.
Stay within your limits to ensure smooth processing of your requests.

System Dashboard: {{ $appUrl }}