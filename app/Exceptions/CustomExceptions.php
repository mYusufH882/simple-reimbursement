<?php

namespace App\Exceptions;

use Exception;

/**
 * Base API Exception Class
 */
abstract class ApiException extends Exception
{
    protected $statusCode = 500;
    protected $errorCode = 'UNKNOWN_ERROR';
    protected $context = [];

    public function __construct(string $message = '', array $context = [], \Throwable $previous = null)
    {
        $this->context = $context;
        parent::__construct($message, 0, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function render($request)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'error_code' => $this->getErrorCode(),
                'error' => $this->getMessage(),
                'context' => $this->getContext(),
                'errors' => null,
                'data' => null
            ], $this->getStatusCode());
        }

        return null; // Let Laravel handle non-API requests
    }
}

/**
 * Business Logic Exception (400)
 */
class BusinessLogicException extends ApiException
{
    protected $statusCode = 400;
    protected $errorCode = 'BUSINESS_LOGIC_ERROR';
}

/**
 * Monthly Limit Exceeded Exception (422)
 */
class MonthlyLimitExceededException extends ApiException
{
    protected $statusCode = 422;
    protected $errorCode = 'MONTHLY_LIMIT_EXCEEDED';

    public function __construct(string $message = 'Monthly limit exceeded', array $limitInfo = [], \Throwable $previous = null)
    {
        $this->context = [
            'limit_info' => $limitInfo,
            'help' => 'Check your monthly usage or try a different category'
        ];

        parent::__construct($message, $this->context, $previous);
    }
}

/**
 * Invalid Reimbursement Status Exception (422)
 */
class InvalidReimbursementStatusException extends ApiException
{
    protected $statusCode = 422;
    protected $errorCode = 'INVALID_REIMBURSEMENT_STATUS';

    public function __construct(string $currentStatus, string $message = '', \Throwable $previous = null)
    {
        $defaultMessage = "Cannot perform this action on reimbursement with status: {$currentStatus}";
        $message = $message ?: $defaultMessage;

        $this->context = [
            'current_status' => $currentStatus,
            'allowed_statuses' => ['pending'],
            'help' => 'Only pending reimbursements can be modified'
        ];

        parent::__construct($message, $this->context, $previous);
    }
}

/**
 * File Upload Exception (413/422)
 */
class FileUploadException extends ApiException
{
    protected $statusCode = 422;
    protected $errorCode = 'FILE_UPLOAD_ERROR';

    public function __construct(string $message = 'File upload failed', array $fileInfo = [], \Throwable $previous = null)
    {
        $this->context = [
            'file_info' => $fileInfo,
            'help' => 'Ensure file is under 5MB and in allowed format (JPG, PNG, PDF)'
        ];

        parent::__construct($message, $this->context, $previous);
    }
}

/**
 * Ownership Exception (403)
 */
class OwnershipException extends ApiException
{
    protected $statusCode = 403;
    protected $errorCode = 'OWNERSHIP_ERROR';

    public function __construct(string $resource = 'resource', \Throwable $previous = null)
    {
        $message = "You can only access your own {$resource}";

        $this->context = [
            'resource' => $resource,
            'help' => 'Employees can only access their own data'
        ];

        parent::__construct($message, $this->context, $previous);
    }
}

/**
 * Category In Use Exception (422)
 */
class CategoryInUseException extends ApiException
{
    protected $statusCode = 422;
    protected $errorCode = 'CATEGORY_IN_USE';

    public function __construct(string $categoryName, int $reimbursementCount, \Throwable $previous = null)
    {
        $message = "Cannot delete category '{$categoryName}' because it has {$reimbursementCount} existing reimbursements";

        $this->context = [
            'category_name' => $categoryName,
            'reimbursement_count' => $reimbursementCount,
            'help' => 'Remove all reimbursements from this category before deleting it'
        ];

        parent::__construct($message, $this->context, $previous);
    }
}

/**
 * Insufficient Permissions Exception (403)
 */
class InsufficientPermissionsException extends ApiException
{
    protected $statusCode = 403;
    protected $errorCode = 'INSUFFICIENT_PERMISSIONS';

    public function __construct(string $requiredRole, string $currentRole, \Throwable $previous = null)
    {
        $message = "Access denied. Required role: {$requiredRole}, current role: {$currentRole}";

        $this->context = [
            'required_role' => $requiredRole,
            'current_role' => $currentRole,
            'help' => 'Contact admin to request appropriate permissions'
        ];

        parent::__construct($message, $this->context, $previous);
    }
}

/**
 * Database Constraint Exception (422)
 */
class DatabaseConstraintException extends ApiException
{
    protected $statusCode = 422;
    protected $errorCode = 'DATABASE_CONSTRAINT_ERROR';

    public function __construct(string $constraint, string $message = '', \Throwable $previous = null)
    {
        $defaultMessage = "Database constraint violation: {$constraint}";
        $message = $message ?: $defaultMessage;

        $this->context = [
            'constraint' => $constraint,
            'help' => 'Check for duplicate values or missing required fields'
        ];

        parent::__construct($message, $this->context, $previous);
    }
}

/**
 * Email Service Exception (500)
 */
class EmailServiceException extends ApiException
{
    protected $statusCode = 500;
    protected $errorCode = 'EMAIL_SERVICE_ERROR';

    public function __construct(string $message = 'Email service error', array $emailData = [], \Throwable $previous = null)
    {
        $this->context = [
            'email_data' => $emailData,
            'help' => 'Email notification failed but action was completed successfully'
        ];

        parent::__construct($message, $this->context, $previous);
    }
}

/**
 * Rate Limit Exception (429)
 */
class RateLimitException extends ApiException
{
    protected $statusCode = 429;
    protected $errorCode = 'RATE_LIMIT_EXCEEDED';

    public function __construct(int $retryAfter = 60, \Throwable $previous = null)
    {
        $message = "Too many requests. Please try again in {$retryAfter} seconds";

        $this->context = [
            'retry_after' => $retryAfter,
            'help' => 'Reduce request frequency to avoid rate limiting'
        ];

        parent::__construct($message, $this->context, $previous);
    }
}

/**
 * External Service Exception (503)
 */
class ExternalServiceException extends ApiException
{
    protected $statusCode = 503;
    protected $errorCode = 'EXTERNAL_SERVICE_ERROR';

    public function __construct(string $service, string $message = '', \Throwable $previous = null)
    {
        $defaultMessage = "External service '{$service}' is currently unavailable";
        $message = $message ?: $defaultMessage;

        $this->context = [
            'service' => $service,
            'help' => 'This is a temporary issue. Please try again later'
        ];

        parent::__construct($message, $this->context, $previous);
    }
}
