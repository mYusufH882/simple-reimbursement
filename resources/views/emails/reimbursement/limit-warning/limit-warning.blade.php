<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reimbursement Limit Warning</title>
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
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: #212529;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.8;
        }
        
        .content {
            padding: 30px;
        }
        
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .warning-message {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            color: #856404;
        }
        
        .warning-message h2 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .limit-stats {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        
        .stat-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .stat-row:last-child {
            border-bottom: none;
        }
        
        .stat-label {
            font-weight: 600;
            color: #495057;
        }
        
        .stat-value {
            color: #212529;
            font-weight: bold;
        }
        
        .used-amount {
            color: #dc3545;
            font-size: 18px;
        }
        
        .remaining-amount {
            color: #28a745;
            font-size: 18px;
        }
        
        .limit-amount {
            color: #6c757d;
            font-size: 18px;
        }
        
        .progress-bar {
            background-color: #e9ecef;
            border-radius: 10px;
            height: 20px;
            margin: 15px 0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: bold;
        }
        
        .progress-warning {
            background: linear-gradient(90deg, #ffc107 0%, #fd7e14 100%);
        }
        
        .progress-danger {
            background: linear-gradient(90deg, #dc3545 0%, #c82333 100%);
        }
        
        .monthly-breakdown {
            background-color: #e7f3ff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        
        .monthly-breakdown h3 {
            color: #0056b3;
            margin-bottom: 15px;
        }
        
        .breakdown-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #b3d7ff;
        }
        
        .breakdown-item:last-child {
            border-bottom: none;
        }
        
        .tips-section {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .tips-section h3 {
            color: #155724;
            margin-bottom: 12px;
        }
        
        .tips-section ul {
            list-style: none;
            padding-left: 0;
        }
        
        .tips-section li {
            padding: 8px 0;
            padding-left: 20px;
            position: relative;
            color: #155724;
        }
        
        .tips-section li:before {
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
        
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        .btn-primary {
            background-color: #007bff;
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
            color: #ffc107;
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
            
            .stat-row, .breakdown-item {
                flex-direction: column;
                text-align: left;
            }
            
            .stat-value {
                margin-top: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>⚠️ Reimbursement Limit Warning</h1>
            <p>You're approaching your monthly limit</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                <strong>Dear {{ $employee->name }},</strong>
            </div>
            
            <div class="warning-message">
                <h2>📊 Monthly Limit Alert</h2>
                <p>This is a friendly reminder that you're approaching your monthly reimbursement limit. Please review your current usage below.</p>
            </div>

            <!-- Limit Statistics -->
            <div class="limit-stats">
                <div class="stat-row">
                    <span class="stat-label">Monthly Limit:</span>
                    <span class="stat-value limit-amount">{{ 'IDR ' . number_format($monthlyLimit ?? 5000000, 0, ',', '.') }}</span>
                </div>
                
                <div class="stat-row">
                    <span class="stat-label">Used This Month:</span>
                    <span class="stat-value used-amount">{{ 'IDR ' . number_format($usedAmount ?? 0, 0, ',', '.') }}</span>
                </div>
                
                <div class="stat-row">
                    <span class="stat-label">Remaining:</span>
                    <span class="stat-value remaining-amount">{{ 'IDR ' . number_format(($monthlyLimit ?? 5000000) - ($usedAmount ?? 0), 0, ',', '.') }}</span>
                </div>
                
                <div class="stat-row">
                    <span class="stat-label">Usage Percentage:</span>
                    <span class="stat-value">{{ round((($usedAmount ?? 0) / ($monthlyLimit ?? 5000000)) * 100, 1) }}%</span>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="progress-bar">
                @php
                    $percentage = round((($usedAmount ?? 0) / ($monthlyLimit ?? 5000000)) * 100, 1);
                    $progressClass = $percentage >= 90 ? 'progress-danger' : 'progress-warning';
                @endphp
                <div class="progress-fill {{ $progressClass }}" style="width: {{ min($percentage, 100) }}%">
                    {{ $percentage }}%
                </div>
            </div>

            <!-- Monthly Breakdown -->
            <div class="monthly-breakdown">
                <h3>📅 This Month's Activity</h3>
                <div class="breakdown-item">
                    <span>Current Month:</span>
                    <span><strong>{{ now()->format('F Y') }}</strong></span>
                </div>
                <div class="breakdown-item">
                    <span>Approved Requests:</span>
                    <span>{{ $approvedCount ?? 0 }} requests</span>
                </div>
                <div class="breakdown-item">
                    <span>Pending Requests:</span>
                    <span>{{ $pendingCount ?? 0 }} requests</span>
                </div>
                <div class="breakdown-item">
                    <span>Days Remaining:</span>
                    <span>{{ now()->daysInMonth - now()->day }} days</span>
                </div>
            </div>

            <!-- Tips Section -->
            <div class="tips-section">
                <h3>💡 Tips for Managing Your Reimbursement Limit</h3>
                <ul>
                    <li>Plan your expenses carefully for the rest of the month</li>
                    <li>Prioritize essential business expenses</li>
                    <li>Consider if any expenses can be postponed to next month</li>
                    <li>Check with your manager about limit adjustments if needed</li>
                    <li>Keep track of your submissions in the dashboard</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ $appUrl }}/employee/reimbursements" class="btn btn-warning">
                    📊 View My Requests
                </a>
                <a href="{{ $appUrl }}/employee/dashboard" class="btn btn-primary">
                    🏠 Go to Dashboard
                </a>
            </div>

            <p><strong>Important:</strong> This limit resets on the 1st of each month. If you need to exceed your limit for essential business expenses, please contact your manager or HR department.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>⚠️ This is an automated reminder from the Reimbursement Management System.</p>
            <p>Stay within your limits to ensure smooth processing of your requests.</p>
            <p><a href="{{ $appUrl }}">Visit System Dashboard</a></p>
        </div>
    </div>
</body>
</html>