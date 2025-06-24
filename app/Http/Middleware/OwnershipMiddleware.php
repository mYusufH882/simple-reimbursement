<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OwnershipMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->isManager()) {
            return $next($request);
        }

        $reimbursementId = $request->route('id') ?? $request->route('reimbursement');

        if ($reimbursementId) {
            $reimbursement = \App\Models\Reimbursement::find($reimbursementId);

            if (!$reimbursement || $reimbursement->user_id !== $user->id) {
                return response()->json([
                    'message' => 'Forbidden. You can only access your own reimbursements',
                ], 403);
            }
        } else {
            return response()->json([
                'message' => 'Forbidden. No reimbursement specified.',
            ], 403);
        }

        return $next($request);
    }
}
