<?php

namespace Pijler\PersonalTokens\Actions;

use Pijler\PersonalTokens\Models\PersonalToken;
use Pijler\PersonalTokens\TokenCreator;

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
