<?php

use App\Http\Middleware\OwnershipMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\ApiException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'owner' => OwnershipMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // ==========================================
        // GLOBAL API ERROR HANDLING
        // ==========================================

        $exceptions->render(function (Throwable $e, Request $request) {
            // Only handle API requests (requests that accept JSON or start with /api)
            if (!$request->expectsJson() && !$request->is('api/*')) {
                return null; // Let Laravel handle web requests normally
            }

            // Get HTTP status code
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            // Base error response structure
            $response = [
                'success' => false,
                'message' => 'An error occurred',
                'error' => null,
                'errors' => null,
                'data' => null
            ];

            // Add debug information in development
            if (config('app.debug')) {
                $response['debug'] = [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ];
            }

            // Handle specific exception types
            switch (true) {

                // ==========================================
                // CUSTOM API EXCEPTIONS (from our ApiException class)
                // ==========================================
                case $e instanceof ApiException:
                    $response['message'] = $e->getMessage();
                    $response['error_code'] = $e->getErrorCode();
                    $response['error'] = $e->getMessage();
                    $response['context'] = $e->getContext();
                    $statusCode = $e->getStatusCode();
                    break;

                // ==========================================
                // AUTHENTICATION ERRORS (401)
                // ==========================================
                case $e instanceof AuthenticationException:
                    $response['message'] = 'Authentication required';
                    $response['error'] = 'Please login to access this resource';
                    $statusCode = 401;
                    break;

                // ==========================================
                // AUTHORIZATION ERRORS (403)
                // ==========================================
                case $e instanceof AuthorizationException:
                    $response['message'] = 'Access forbidden';
                    $response['error'] = 'You do not have permission to access this resource';
                    $statusCode = 403;
                    break;

                // ==========================================
                // VALIDATION ERRORS (422)
                // ==========================================
                case $e instanceof ValidationException:
                    $response['message'] = 'Validation failed';
                    $response['error'] = 'The given data was invalid';
                    $response['errors'] = $e->errors();
                    $statusCode = 422;
                    break;

                // ==========================================
                // MODEL NOT FOUND ERRORS (404)
                // ==========================================
                case $e instanceof ModelNotFoundException:
                    $modelName = class_basename($e->getModel());
                    $response['message'] = "{$modelName} not found";
                    $response['error'] = "The requested {$modelName} could not be found";
                    $statusCode = 404;
                    break;

                // ==========================================
                // ROUTE NOT FOUND ERRORS (404)
                // ==========================================
                case $e instanceof NotFoundHttpException:
                    $response['message'] = 'Endpoint not found';
                    $response['error'] = 'The requested API endpoint does not exist';
                    $statusCode = 404;
                    break;

                // ==========================================
                // METHOD NOT ALLOWED ERRORS (405)
                // ==========================================
                case $e instanceof MethodNotAllowedHttpException:
                    $response['message'] = 'Method not allowed';
                    $response['error'] = 'The HTTP method used is not allowed for this endpoint';
                    $statusCode = 405;
                    break;

                // ==========================================
                // RATE LIMIT ERRORS (429)
                // ==========================================
                case $e instanceof TooManyRequestsHttpException:
                    $response['message'] = 'Too many requests';
                    $response['error'] = 'Rate limit exceeded. Please try again later';
                    $statusCode = 429;
                    break;

                // ==========================================
                // DATABASE ERRORS (500)
                // ==========================================
                case $e instanceof \Illuminate\Database\QueryException:
                    $response['message'] = 'Database error';
                    $response['error'] = config('app.debug')
                        ? $e->getMessage()
                        : 'A database error occurred';
                    $statusCode = 500;
                    break;

                // ==========================================
                // FILE UPLOAD ERRORS (413/422)
                // ==========================================
                case $e instanceof \Illuminate\Http\Exceptions\PostTooLargeException:
                    $response['message'] = 'File too large';
                    $response['error'] = 'The uploaded file exceeds the maximum allowed size';
                    $statusCode = 413;
                    break;

                // ==========================================
                // CUSTOM BUSINESS LOGIC ERRORS (400)
                // ==========================================
                case $e instanceof \InvalidArgumentException:
                case $e instanceof \LogicException:
                    $response['message'] = 'Business logic error';
                    $response['error'] = $e->getMessage();
                    $statusCode = 400;
                    break;

                // ==========================================
                // GENERIC HTTP EXCEPTIONS
                // ==========================================
                case method_exists($e, 'getStatusCode'):
                    $response['message'] = 'HTTP error';
                    $response['error'] = $e->getMessage() ?: 'An HTTP error occurred';
                    break;

                // ==========================================
                // UNKNOWN ERRORS (500)
                // ==========================================
                default:
                    $response['message'] = 'Internal server error';
                    $response['error'] = config('app.debug')
                        ? $e->getMessage()
                        : 'An unexpected error occurred';
                    $statusCode = 500;
                    break;
            }

            // Log errors for monitoring (except 4xx client errors)
            if ($statusCode >= 500) {
                \Illuminate\Support\Facades\Log::error('API Error', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user_id' => auth()->id(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            }

            return response()->json($response, $statusCode);
        });

        // ==========================================
        // CUSTOM ERROR REPORTING
        // ==========================================

        $exceptions->report(function (Throwable $e) {
            // Custom reporting logic if needed
            // For example, send to external monitoring service

            // Only report server errors and critical business logic errors
            if (
                $e instanceof \Illuminate\Database\QueryException ||
                $e instanceof \ErrorException ||
                $e instanceof \Error
            ) {

                // You can integrate with services like Sentry, Bugsnag, etc.
                // \Sentry\captureException($e);

                return true; // Continue with default reporting
            }

            return false; // Don't report client errors (4xx)
        });
    })->create();
