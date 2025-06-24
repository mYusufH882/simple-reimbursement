<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Reimbursements Reminder</title>
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
            background: linear-gradient(135deg, #fd7e14 0%, #e55100 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .urgent-counter {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            padding: 10px 20px;
            margin: 15px 0;
            display: inline-block;
            font-weight: bold;
            font-size: 18px;
        }
        
        .content {
            padding: 30px;
        }
        
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .reminder-alert {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #fd7e14;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            color: #856404;
        }
        
        .reminder-alert h2 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        
        .stat-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            border-left: 4px solid #fd7e14;
        }
        
        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #fd7e14;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .pending-list {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .pending-item {
            background-color: white;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 12px;
            border-left: 4px solid #ffc107;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .pending-item:last-child {
            margin-bottom: 0;
        }
        
        .pending-item.urgent {
            border-left-color: #dc3545;
            background-color: #fff5f5;
        }
        
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .item-title {
            font-weight: 600;
            color: #212529;
        }
        
        .item-amount {
            font-weight: bold;
            color: #28a745;
            font-size: 16px;
        }
        
        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            color: #6c757d;
        }
        
        .item-employee {
            font-weight: 500;
        }
        
        .days-pending {
            color: #dc3545;
            font-weight: 600;
        }
        
        .days-pending.urgent {
            background-color: #dc3545;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .oldest-alert {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #721c24;
        }
        
        .priority-notice {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .priority-notice h3 {
            color: #0c5460;
            margin-bottom: 12px;
        }
        
        .priority-notice ul {
            list-style: none;
            padding-left: 0;
        }
        
        .priority-notice li {
            padding: 8px 0;
            padding-left: 20px;
            position: relative;
            color: #0c5460;
        }
        
        .priority-notice li:before {
            content: "⚡";
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
            background-color: #fd7e14;
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
            color: #fd7e14;
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
                grid-template-columns: 1fr;
            }
            
            .item-header, .item-details {
                flex-direction: column;
                text-align: left;
            }
            
            .item-amount {
                margin-top: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>⏰ Pending Reimbursements Reminder</h1>
            <p>Action required for pending approvals</p>
            <div class="urgent-counter">
                {{ $pendingCount }} pending {{ $pendingCount === 1 ? 'request' : 'requests' }}
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                <strong>Dear {{ $manager->name }},</strong>
            </div>
            
            <div class="reminder-alert">
                <h2>📋 Pending Approvals Need Your Attention</h2>
                <p>You have <strong>{{ $pendingCount }}</strong> reimbursement {{ $pendingCount === 1 ? 'request' : 'requests' }} waiting for your review and approval.</p>
            </div>

            <!-- Statistics Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">{{ $pendingCount }}</div>
                    <div class="stat-label">Total Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ $urgentCount }}</div>
                    <div class="stat-label">Urgent (3+ days)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ $totalPendingAmount }}</div>
                    <div class="stat-label">Total Amount</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">{{ $oldestPending ? $oldestPending['days_pending'] : 0 }}</div>
                    <div class="stat-label">Oldest (days)</div>
                </div>
            </div>

            <!-- Oldest Pending Alert -->
            @if($oldestPending && $oldestPending['days_pending'] > 3)
            <div class="oldest-alert">
                <strong>🚨 Urgent:</strong> The oldest pending request is <strong>{{ $oldestPending['days_pending'] }} days</strong> old. 
                Please prioritize "{{ $oldestPending['title'] }}" from {{ $oldestPending['employee_name'] ?? 'Unknown Employee' }}.
            </div>
            @endif

            <!-- Pending Reimbursements List -->
            <div class="pending-list">
                <h3 style="margin-bottom: 15px; color: #495057;">📄 Pending Requests</h3>
                
                @foreach($pendingReimbursements as $reimbursement)
                @php
                    $daysPending = \Carbon\Carbon::parse($reimbursement['submitted_at'])->diffInDays(now());
                    $isUrgent = $daysPending >= 3;
                @endphp
                
                <div class="pending-item {{ $isUrgent ? 'urgent' : '' }}">
                    <div class="item-header">
                        <span class="item-title">{{ $reimbursement['title'] }}</span>
                        <span class="item-amount">{{ 'IDR ' . number_format($reimbursement['amount'], 0, ',', '.') }}</span>
                    </div>
                    <div class="item-details">
                        <span class="item-employee">{{ $reimbursement['employee_name'] ?? 'Unknown' }}</span>
                        <span class="days-pending {{ $isUrgent ? 'urgent' : '' }}">
                            {{ $daysPending }} {{ $daysPending === 1 ? 'day' : 'days' }} pending
                        </span>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Priority Notice -->
            <div class="priority-notice">
                <h3>⚡ Action Items:</h3>
                <ul>
                    <li>Review and approve/reject pending requests promptly</li>
                    <li>Prioritize requests that are 3+ days old</li>
                    <li>Check supporting documents before making decisions</li>
                    <li>Provide clear reasons for any rejections</li>
                    <li>Contact employees if additional information is needed</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ $appUrl }}/manager/reimbursements?status=pending" class="btn btn-primary">
                    📋 Review All Pending
                </a>
                <a href="{{ $appUrl }}/manager/dashboard" class="btn btn-success">
                    🏠 Manager Dashboard
                </a>
            </div>

            <p><strong>Important:</strong> Timely review of reimbursement requests helps maintain employee satisfaction and ensures smooth expense processing. Please try to review requests within 2-3 business days.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>⏰ This is an automated reminder from the Reimbursement Management System.</p>
            <p>Sent to help you stay on top of pending approvals.</p>
            <p><a href="{{ $appUrl }}">Manager Dashboard</a></p>
        </div>
    </div>
</body>
</html>