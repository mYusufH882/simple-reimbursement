<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reimbursement Rejected</title>
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
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
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
        
        .content {
            padding: 30px;
        }
        
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .rejection-message {
            background-color: #f8d7da;
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            color: #721c24;
        }
        
        .rejection-message h2 {
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
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .reason-section h3 {
            color: #856404;
            margin-bottom: 12px;
        }
        
        .reason-text {
            background-color: white;
            border-radius: 4px;
            padding: 15px;
            color: #495057;
            font-style: italic;
            border-left: 4px solid #ffc107;
        }
        
        .rejector-info {
            background-color: #e7f3ff;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #007bff;
        }
        
        .next-steps {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .next-steps h3 {
            color: #0c5460;
            margin-bottom: 12px;
        }
        
        .next-steps ul {
            list-style: none;
            padding-left: 0;
        }
        
        .next-steps li {
            padding: 8px 0;
            padding-left: 20px;
            position: relative;
            color: #0c5460;
        }
        
        .next-steps li:before {
            content: "📝";
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
            background-color: #007bff;
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
            color: #007bff;
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
            <h1>❌ Reimbursement Rejected</h1>
            <p>Your request requires attention</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                <strong>Dear {{ $employee->name }},</strong>
            </div>
            
            <div class="rejection-message">
                <h2>📋 Request Status Update</h2>
                <p>We regret to inform you that your reimbursement request has been <strong>rejected</strong>. Please review the details and reason below.</p>
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
                    <span class="info-value">{{ $rejectionDate ?? $reimbursement->updated_at->format('d F Y, H:i') }}</span>
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
                <h3>📝 Reason for Rejection:</h3>
                <div class="reason-text">
                    "{{ $reason }}"
                </div>
            </div>
            @endif

            <!-- Rejector Information -->
            @if(isset($rejector))
            <div class="rejector-info">
                <strong>👤 Rejected by:</strong> {{ $rejector->name ?? 'Manager' }}
                <br>
                <small>{{ $rejectionDate ?? $reimbursement->updated_at->format('l, d F Y \a\t H:i') }}</small>
            </div>
            @endif

            <!-- Next Steps -->
            <div class="next-steps">
                <h3>🔄 What you can do next:</h3>
                <ul>
                    <li>Review the rejection reason carefully</li>
                    <li>Gather additional documentation if needed</li>
                    <li>Contact your manager for clarification</li>
                    <li>Submit a new request with corrections</li>
                    <li>Reach out to HR if you need assistance</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ $appUrl }}/employee/reimbursements/create" class="btn btn-success">
                    ➕ Submit New Request
                </a>
                <a href="{{ $appUrl }}/employee/reimbursements/{{ $reimbursement->id }}" class="btn btn-primary">
                    📄 View Details
                </a>
            </div>

            <p><strong>Need Help?</strong> If you believe this rejection was made in error or need clarification on the company's reimbursement policy, please don't hesitate to contact your manager or the HR department.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>We're here to help you get your expenses reimbursed properly.</p>
            <p>This is an automated email from the Reimbursement Management System.</p>
            <p><a href="{{ $appUrl }}">Visit System Dashboard</a></p>
        </div>
    </div>
</body>
</html>