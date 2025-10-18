<?php

namespace PersonalTokens\Middleware;

use Closure;
use Illuminate\Http\Request;
use PersonalTokens\Actions\ValidPersonalToken;

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

        if (! ValidPersonalToken::handle($token, $type)) {
            abort(401, trans('Invalid or expired personal token.'));
        }

        return $next($request);
    }
}
