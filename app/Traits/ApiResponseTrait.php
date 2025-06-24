<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponseTrait
{
    protected function successResponse($data = null, $message = 'Success', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'status_code' => $statusCode,
            ],
        ];

        if ($data instanceof ResourceCollection && $data->resource instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $paginator = $data->resource;
            $response['meta']['pagination'] = [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ];
        }

        return response()->json($response, $statusCode);
    }

    protected function errorResponse($message = 'Error occured', int $statusCode = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'status_code' => $statusCode,
            ],
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    protected function validationErrorResponse($errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }

    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    protected function createdResponse($data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    protected function updatedResponse($data = null, string $message = 'Resource updated successfully'): JsonResponse
    {
        return $this->successResponse($data, $message, 200);
    }

    protected function deletedResponse(string $message = 'Resource deleted successfully'): JsonResponse
    {
        return $this->successResponse(null, $message, 200);
    }
}
