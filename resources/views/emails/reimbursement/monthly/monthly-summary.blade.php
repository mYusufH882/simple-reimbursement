<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Reimbursement Summary</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }
        
        .container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 26px;
            margin-bottom: 8px;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .month-badge {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
            display: inline-block;
        }
        
        .content {
            padding: 30px;
        }
        
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .summary-intro {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border: 2px solid #17a2b8;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            color: #0d47a1;
        }
        
        .summary-intro h2 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 25px 0;
        }
        
        .stat-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            border-left: 4px solid #17a2b8;
            transition: transform 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #17a2b8;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 13px;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .stat-card.approved .stat-number {
            color: #28a745;
        }
        
        .stat-card.rejected .stat-number {
            color: #dc3545;
        }
        
        .stat-card.pending .stat-number {
            color: #ffc107;
        }
        
        .breakdown-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
        }
        
        .breakdown-title {
            font-size: 18px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 15px;
            border-bottom: 2px solid #17a2b8;
            padding-bottom: 8px;
        }
        
        .category-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .category-item:last-child {
            border-bottom: none;
        }
        
        .category-name {
            font-weight: 500;
            color: #495057;
        }
        
        .category-stats {
            display: flex;
            gap: 15px;
            font-size: 14px;
        }
        
        .category-amount {
            font-weight: bold;
            color: #28a745;
        }
        
        .category-count {
            color: #6c757d;
        }
        
        .employees-section {
            background-color: #e7f3ff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .employee-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #b3d7ff;
        }
        
        .employee-item:last-child {
            border-bottom: none;
        }
        
        .employee-name {
            font-weight: 500;
            color: #0056b3;
        }
        
        .employee-amount {
            font-weight: bold;
            color: #28a745;
        }
        
        .processing-stats {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .processing-stats h3 {
            color: #856404;
            margin-bottom: 15px;
        }
        
        .processing-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            color: #856404;
        }
        
        .insights-section {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .insights-section h3 {
            color: #155724;
            margin-bottom: 12px;
        }
        
        .insights-section ul {
            list-style: none;
            padding-left: 0;
        }
        
        .insights-section li {
            padding: 8px 0;
            padding-left: 20px;
            position: relative;
            color: #155724;
        }
        
        .insights-section li:before {
            content: "💡";
            position: absolute;
            left: 0;
        }
        
        .action-buttons {
            text-align: center;
            margin: 30px 0;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 8px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: #17a2b8;
            color: white;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        
        .footer p {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .footer a {
            color: #17a2b8;
            text-decoration: none;
        }
        
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                border-radius: 8px;
            }
            
            .header, .content {
                padding: 20px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .category-item, .employee-item {
                flex-direction: column;
                text-align: left;
                gap: 8px;
            }
            
            .category-stats {
                justify-content: space-between;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📊 Monthly Reimbursement Summary</h1>
            <p>Comprehensive overview of reimbursement activities</p>
            <div class="month-badge">{{ $monthYear }}</div>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                <strong>Dear {{ $manager->name }},</strong>
            </div>
            
            <div class="summary-intro">
                <h2>📈 Monthly Performance Overview</h2>
                <p>Here's a comprehensive summary of reimbursement activities for <strong>{{ $monthYear }}</strong>. This report includes submission statistics, processing performance, and insights to help improve the reimbursement process.</p>
            </div>

            <!-- Main Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">{{ $totalSubmissions }}</div>
                    <div class="stat-label">Total Submissions</div>
                </div>
                <div class="stat-card approved">
                    <div class="stat-number">{{ $approvedCount }}</div>
                    <div class="stat-label">Approved</div>
                </div>
                <div class="stat-card rejected">
                    <div class="stat-number">{{ $rejectedCount }}</div>
                    <div class="stat-label">Rejected</div>
                </div>
                <div class="stat-card pending">
                    <div class="stat-number">{{ $pendingCount }}</div>
                    <div class="stat-label">Still Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ $totalAmount }}</div>
                    <div class="stat-label">Total Amount</div>
                </div>
                <div class="stat-card approved">
                    <div class="stat-number">{{ $approvedAmount }}</div>
                    <div class="stat-label">Approved Amount</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ $averageAmount }}</div>
                    <div class="stat-label">Average Request</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ round(($approvedCount / max($totalSubmissions, 1)) * 100) }}%</div>
                    <div class="stat-label">Approval Rate</div>
                </div>
            </div>

            <!-- Category Breakdown -->
            @if(!empty($categoryBreakdown))
            <div class="breakdown-section">
                <h3 class="breakdown-title">📋 Category Breakdown</h3>
                @foreach($categoryBreakdown as $category)
                <div class="category-item">
                    <span class="category-name">{{ $category['name'] ?? 'Unknown Category' }}</span>
                    <div class="category-stats">
                        <span class="category-amount">{{ 'IDR ' . number_format($category['total_amount'] ?? 0, 0, ',', '.') }}</span>
                        <span class="category-count">{{ $category['count'] ?? 0 }} requests</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Top Employees -->
            @if(!empty($topEmployees))
            <div class="employees-section">
                <h3 class="breakdown-title">🏆 Top Employees by Amount</h3>
                @foreach($topEmployees as $employee)
                <div class="employee-item">
                    <span class="employee-name">{{ $employee['name'] ?? 'Unknown Employee' }}</span>
                    <span class="employee-amount">{{ 'IDR ' . number_format($employee['total_amount'] ?? 0, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Processing Statistics -->
            @if(!empty($processingStats))
            <div class="processing-stats">
                <h3>⏱️ Processing Performance</h3>
                @foreach($processingStats as $key => $stat)
                <div class="processing-item">
                    <span>{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                    <span><strong>{{ $stat }}</strong></span>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Insights & Recommendations -->
            <div class="insights-section">
                <h3>💡 Insights & Recommendations</h3>
                <ul>
                    @if($totalSubmissions > 0)
                    <li>{{ $approvedCount }} out of {{ $totalSubmissions }} requests were approved ({{ round(($approvedCount / $totalSubmissions) * 100) }}% approval rate)</li>
                    @endif
                    
                    @if($pendingCount > 0)
                    <li>{{ $pendingCount }} requests are still pending - consider reviewing them promptly</li>
                    @endif
                    
                    @if(!empty($categoryBreakdown))
                    @php $topCategory = collect($categoryBreakdown)->sortByDesc('total_amount')->first(); @endphp
                    <li>{{ $topCategory['name'] ?? 'Unknown' }} had the highest total amount this month</li>
                    @endif
                    
                    @if($rejectedCount > 0)
                    <li>Monitor rejection reasons to identify training opportunities</li>
                    @endif
                    
                    <li>Regular summary reports help track reimbursement trends and improve processes</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ $appUrl }}/manager/reports" class="btn btn-primary">
                    📊 Detailed Reports
                </a>
                <a href="{{ $appUrl }}/manager/reimbursements" class="btn btn-success">
                    📋 All Reimbursements
                </a>
            </div>

            <p><strong>Note:</strong> This summary is automatically generated monthly to help you track reimbursement activities and identify areas for improvement. If you have questions about any statistics, please contact the system administrator.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>📊 This is an automated monthly summary from the Reimbursement Management System.</p>
            <p>Generated on {{ now()->format('d F Y \a\t H:i') }}</p>
            <p><a href="{{ $appUrl }}">Manager Dashboard</a></p>
        </div>
    </div>
</body>
</html>