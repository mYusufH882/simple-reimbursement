MONTHLY REIMBURSEMENT SUMMARY - {{ $monthYear }}

Dear {{ $manager->name }},

Here's a comprehensive summary of reimbursement activities for {{ $monthYear }}.

MONTHLY PERFORMANCE OVERVIEW:
=============================
This report includes submission statistics, processing performance, and insights 
to help improve the reimbursement process.

MAIN STATISTICS:
===============
Total Submissions: {{ $totalSubmissions }}
Approved: {{ $approvedCount }}
Rejected: {{ $rejectedCount }}
Still Pending: {{ $pendingCount }}
Total Amount: {{ $totalAmount }}
Approved Amount: {{ $approvedAmount }}
Average Request: {{ $averageAmount }}
Approval Rate: {{ round(($approvedCount / max($totalSubmissions, 1)) * 100) }}%

@if(!empty($categoryBreakdown))
CATEGORY BREAKDOWN:
==================
@foreach($categoryBreakdown as $category)
{{ $category['name'] ?? 'Unknown Category' }}:
  Amount: {{ 'IDR ' . number_format($category['total_amount'] ?? 0, 0, ',', '.') }}
  Requests: {{ $category['count'] ?? 0 }}

@endforeach
@endif

@if(!empty($topEmployees))
TOP EMPLOYEES BY AMOUNT:
=======================
@foreach($topEmployees as $employee)
{{ $employee['name'] ?? 'Unknown Employee' }}: {{ 'IDR ' . number_format($employee['total_amount'] ?? 0, 0, ',', '.') }}
@endforeach

@endif

@if(!empty($processingStats))
PROCESSING PERFORMANCE:
======================
@foreach($processingStats as $key => $stat)
{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $stat }}
@endforeach

@endif

INSIGHTS & RECOMMENDATIONS:
===========================
@if($totalSubmissions > 0)
- {{ $approvedCount }} out of {{ $totalSubmissions }} requests were approved ({{ round(($approvedCount / $totalSubmissions) * 100) }}% approval rate)
@endif

@if($pendingCount > 0)
- {{ $pendingCount }} requests are still pending - consider reviewing them promptly
@endif

@if(!empty($categoryBreakdown))
@php $topCategory = collect($categoryBreakdown)->sortByDesc('total_amount')->first(); @endphp
- {{ $topCategory['name'] ?? 'Unknown' }} had the highest total amount this month
@endif

@if($rejectedCount > 0)
- Monitor rejection reasons to identify training opportunities
@endif

- Regular summary reports help track reimbursement trends and improve processes

QUICK LINKS:
============
Detailed Reports: {{ $appUrl }}/manager/reports
All Reimbursements: {{ $appUrl }}/manager/reimbursements

NOTE: This summary is automatically generated monthly to help you track reimbursement 
activities and identify areas for improvement. If you have questions about any statistics, 
please contact the system administrator.

This is an automated monthly summary from the Reimbursement Management System.
Generated on {{ now()->format('d F Y \a\t H:i') }}

Manager Dashboard: {{ $appUrl }}