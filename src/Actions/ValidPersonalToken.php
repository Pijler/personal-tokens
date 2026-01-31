<?php

namespace PersonalTokens\Actions;

use PersonalTokens\Models\PersonalToken;
use PersonalTokens\TokenCreator;

/**
 * @deprecated Use TokenCreator::validToken() instead.
 */
class ValidPersonalToken
{
    /**
     * Execute the action.
     *
     * @deprecated Use TokenCreator::validToken() instead.
     */
    public static function handle(string $token, mixed $type = null): ?PersonalToken
    {
        return TokenCreator::validToken($token, $type);
    }
}
