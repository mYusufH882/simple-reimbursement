<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reimbursement Approved</title>
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
        
        .celebration {
            text-align: center;
            font-size: 48px;
            margin: 20px 0;
        }
        
        .content {
            padding: 30px;
        }
        
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .approval-message {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: 2px solid #28a745;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        
        .approval-message h2 {
            color: #155724;
            font-size: 20px;
            margin-bottom: 10px;
        }
        
        .approval-message p {
            color: #155724;
            font-size: 16px;
        }
        
        .reimbursement-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
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
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
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
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            background-color: #28a745;
            color: white;
        }
        
        .next-steps {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .next-steps h3 {
            color: #856404;
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
            color: #856404;
        }
        
        .next-steps li:before {
            content: "💰";
            position: absolute;
            left: 0;
        }
        
        .approver-info {
            background-color: #e7f3ff;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #007bff;
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
        
        .btn-success {
            background-color: #28a745;
            color: white;
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
            color: #28a745;
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
            <div class="celebration">🎉</div>
            <h1>Reimbursement Approved!</h1>
            <p>Your request has been successfully approved</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                <strong>Dear {{ $employee->name }},</strong>
            </div>
            
            <div class="approval-message">
                <h2>🎊 Congratulations!</h2>
                <p>Your reimbursement request has been <strong>approved</strong> and will be processed for payment.</p>
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
                    <span class="info-label">Approved Amount:</span>
                    <span class="info-value amount">{{ $formattedAmount }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Submission Date:</span>
                    <span class="info-value">{{ $submissionDate }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Approval Date:</span>
                    <span class="info-value">{{ $approvalDate ?? $reimbursement->updated_at->format('d F Y, H:i') }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        <span class="status">✅ Approved</span>
                    </span>
                </div>
                
                @if($reimbursement->description)
                <div class="info-row">
                    <span class="info-label">Description:</span>
                    <span class="info-value">{{ $reimbursement->description }}</span>
                </div>
                @endif
            </div>

            <!-- Approver Information -->
            @if(isset($approver))
            <div class="approver-info">
                <strong>👤 Approved by:</strong> {{ $approver->name ?? 'Manager' }}
                <br>
                <small>{{ $approvalDate ?? $reimbursement->updated_at->format('l, d F Y \a\t H:i') }}</small>
            </div>
            @endif

            <!-- Next Steps -->
            <div class="next-steps">
                <h3>💼 What happens next?</h3>
                <ul>
                    <li>Your request will be forwarded to the finance team</li>
                    <li>Payment processing typically takes 3-5 business days</li>
                    <li>You'll receive payment via your registered bank account</li>
                    <li>Keep this reference number for your records</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ $appUrl }}/employee/reimbursements/{{ $reimbursement->id }}" class="btn btn-success">
                    📄 View Details
                </a>
                <a href="{{ $appUrl }}/employee/reimbursements" class="btn btn-primary">
                    📋 My Requests
                </a>
            </div>

            <p><strong>Important:</strong> Please allow 3-5 business days for payment processing. If you have any questions about the payment schedule, please contact the finance department.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>🎉 Congratulations on your approved reimbursement!</p>
            <p>This is an automated email from the Reimbursement Management System.</p>
            <p><a href="{{ $appUrl }}">Visit System Dashboard</a></p>
        </div>
    </div>
</body>
</html>