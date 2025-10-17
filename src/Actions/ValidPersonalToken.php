<?php

namespace PersonalTokens\Actions;

use PersonalTokens\Models\PersonalToken;

use function Illuminate\Support\enum_value;

class ValidPersonalToken
{
    /**
     * Execute the action.
     */
    public static function handle(string $token, mixed $type = null): ?PersonalToken
    {
        $type = enum_value($type);

        $token = PersonalToken::findToken($token);

        return self::isValid($type, $token) ? $token : null;
    }

    /**
     * Check if the personal token is valid.
     */
    private static function isValid(mixed $type, ?PersonalToken $token): bool
    {
        $typeValid = is_null($type) || $type === enum_value($token?->type);

        return $typeValid && ! $token?->isUsed() && ! $token?->isExpired();
    }
}
