<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Reimbursement Submission</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        .reimbursement-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
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
            color: #28a745;
        }
        
        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            background-color: #ffc107;
            color: #212529;
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
            background-color: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #5a6fd8;
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
            color: #667eea;
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
            <h1>📋 New Reimbursement Submission</h1>
            <p>Pending Your Review</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                <strong>Dear {{ $manager->name }},</strong>
            </div>
            
            <p>A new reimbursement request has been submitted and is waiting for your review.</p>

            <!-- Reimbursement Details Card -->
            <div class="reimbursement-card">
                <div class="info-row">
                    <span class="info-label">Reference Number:</span>
                    <span class="info-value"><strong>{{ 'REF-' . str_pad($reimbursement->id, 6, '0', STR_PAD_LEFT) }}</strong></span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Employee:</span>
                    <span class="info-value">{{ $employee->name }}</span>
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
                    <span class="info-value">{{ $reimbursement->submitted_at->format('d F Y, H:i') }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        <span class="status">{{ ucfirst($reimbursement->status) }}</span>
                    </span>
                </div>
                
                @if($reimbursement->description)
                <div class="info-row">
                    <span class="info-label">Description:</span>
                    <span class="info-value">{{ $reimbursement->description }}</span>
                </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ $appUrl }}/manager/reimbursements/{{ $reimbursement->id }}" class="btn btn-primary">
                    🔍 Review Details
                </a>
                <a href="{{ $appUrl }}/manager/reimbursements" class="btn btn-primary">
                    📋 View All Requests
                </a>
            </div>

            <p>Please review this request at your earliest convenience. You can approve or reject the request through the system.</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This is an automated email from the Reimbursement Management System.</p>
            <p>If you have any questions, please contact the HR department.</p>
            <p><a href="{{ $appUrl }}">Visit System Dashboard</a></p>
        </div>
    </div>
</body>
</html>