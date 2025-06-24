<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[ADMIN] Reimbursement Rejected</title>
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
            background: linear-gradient(135deg, #6f42c1 0%, #5a2d91 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .admin-badge {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            display: inline-block;
        }
        
        .header h1 {
            font-size: 22px;
            margin-bottom: 8px;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .admin-alert {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            color: #856404;
        }
        
        .admin-alert h2 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .reimbursement-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            min-width: 140px;
        }
        
        .info-value {
            color: #212529;
            flex: 1;
            text-align: right;
        }
        
        .amount {
            font-size: 20px;
            font-weight: bold;
            color: #dc3545;
        }
        
        .reference-number {
            font-family: 'Courier New', monospace;
            background-color: #e9ecef;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            background-color: #dc3545;
            color: white;
        }
        
        .reason-section {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .reason-section h3 {
            color: #721c24;
            margin-bottom: 12px;
        }
        
        .reason-text {
            background-color: white;
            border-radius: 4px;
            padding: 15px;
            color: #495057;
            font-style: italic;
            border-left: 4px solid #dc3545;
        }
        
        .rejector-info {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #ffc107;
        }
        
        .admin-actions {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .admin-actions h3 {
            color: #0c5460;
            margin-bottom: 12px;
        }
        
        .admin-actions ul {
            list-style: none;
            padding-left: 0;
        }
        
        .admin-actions li {
            padding: 8px 0;
            padding-left: 20px;
            position: relative;
            color: #0c5460;
        }
        
        .admin-actions li:before {
            content: "📋";
            position: absolute;
            left: 0;
        }
        
        .policy-notice {
            background-color: #e7f3ff;
            border: 1px solid #b3d7ff;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            color: #0056b3;
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
            background-color: #6f42c1;
            color: white;
        }
        
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
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
            color: #6f42c1;
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
            
            .info-row {
                flex-direction: column;
                text-align: left;
            }
            
            .info-value {
                text-align: left;
                margin-top: 4px;
                font-weight: 600;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="admin-badge">🔒 ADMIN NOTIFICATION</div>
            <h1>Reimbursement Rejected</h1>
            <p>Administrative tracking notification</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                <strong>Dear {{ $admin->name }},</strong>
            </div>
            
            <div class="admin-alert">
                <h2>📊 Administrative Notification</h2>
                <p>A reimbursement request has been <strong>rejected</strong> and requires administrative tracking and policy review.</p>
            </div>

            <!-- Reimbursement Details Card -->
            <div class="reimbursement-card">
                <div class="info-row">
                    <span class="info-label">Reference Number:</span>
                    <span class="info-value">
                        <span class="reference-number">{{ $referenceNumber }}</span>
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Employee:</span>
                    <span class="info-value">{{ $employee->name }} ({{ $employee->email }})</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Title:</span>
                    <span class="info-value">{{ $reimbursement->title }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Category:</span>
                    <span class="info-value">{{ $category->name }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Amount:</span>
                    <span class="info-value amount">{{ $formattedAmount }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Submission Date:</span>
                    <span class="info-value">{{ $submissionDate }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Rejection Date:</span>
                    <span class="info-value">{{ $rejectionDate }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        <span class="status">❌ Rejected</span>
                    </span>
                </div>
                
                @if($reimbursement->description)
                <div class="info-row">
                    <span class="info-label">Description:</span>
                    <span class="info-value">{{ $reimbursement->description }}</span>
                </div>
                @endif
            </div>

            <!-- Rejection Reason -->
            @if($reason)
            <div class="reason-section">
                <h3>📝 Rejection Reason:</h3>
                <div class="reason-text">
                    "{{ $reason }}"
                </div>
            </div>
            @endif

            <!-- Rejector Information -->
            @if(isset($rejector))
            <div class="rejector-info">
                <strong>👤 Rejected by:</strong> {{ $rejector->name }} ({{ $rejector->role }})
                <br>
                <small>{{ $rejectionDate }}</small>
            </div>
            @endif

            <!-- Policy Notice -->
            <div class="policy-notice">
                <strong>📋 Policy Compliance:</strong> Review rejection patterns to ensure consistent policy application and identify training opportunities.
            </div>

            <!-- Admin Actions -->
            <div class="admin-actions">
                <h3>📋 Administrative Actions Required:</h3>
                <ul>
                    <li>Monitor rejection patterns and policy compliance</li>
                    <li>Review employee reimbursement history</li>
                    <li>Track rejection reasons for training purposes</li>
                    <li>Ensure proper communication with employee</li>
                    <li>Update policy documentation if needed</li>
                    <li>Monitor for potential appeals or questions</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ $appUrl }}/admin/reimbursements/{{ $reimbursement->id }}" class="btn btn-primary">
                    🔍 View Full Details
                </a>
                <a href="{{ $appUrl }}/admin/reimbursements" class="btn btn-warning">
                    📊 All Reimbursements
                </a>
            </div>

            <p><strong>Note:</strong> This is an administrative notification for tracking purposes. Monitor employee response and provide support if needed for policy clarification.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>🔒 This is an automated administrative notification from the Reimbursement Management System.</p>
            <p>For admin tracking and policy compliance monitoring.</p>
            <p><a href="{{ $appUrl }}">Admin Dashboard</a></p>
        </div>
    </div>
</body>
</html>