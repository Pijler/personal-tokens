<?php

namespace Pijler\PersonalTokens\Middleware;

use Closure;
use Illuminate\Http\Request;
use Pijler\PersonalTokens\TokenCreator;

class EnsureValidPersonalToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, mixed $type = null)
    {
        $token = $request->input('token');

        // Ensure token is a string, otherwise treat as invalid
        if (! is_string($token)) {
            abort(401, trans('Invalid or expired personal token.'));
        }

        if (! TokenCreator::validToken($token, $type)) {
            abort(401, trans('Invalid or expired personal token.'));
        }

        return $next($request);
    }
}
