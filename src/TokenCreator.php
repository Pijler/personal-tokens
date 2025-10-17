<?php

namespace PersonalTokens;

use Closure;
use Illuminate\Support\Str;
use PersonalTokens\Models\PersonalToken;

class TokenCreator
{
    /**
     * The default expiration time (in minutes) for generated personal tokens.
     *
     * This value determines how long a personal token remains valid
     * before it expires. By default, tokens expire after 1440 minutes (24 hours).
     */
    public static int $expiresAt = 1440;

    /**
     * A custom callback used to generate the plain text representation of the token.
     *
     * If defined, this closure will be executed when generating a new personal token,
     * allowing for custom formatting or encoding of the token value.
     *
     * Example:
     *
     * TokenCreator::plainTextTokenUsing(fn () => Str::random(5));
     */
    public static ?Closure $plainTextToken = null;

    /**
     * The fully qualified class name of the personal token model.
     *
     * This model is responsible for persisting and managing personal tokens
     * in the database. You can override this to use a custom implementation.
     */
    public static string $personalTokenModel = PersonalToken::class;

    /**
     * Set the default expiration time (in minutes) for generated personal tokens.
     */
    public static function expiresAt(int $minutes): void
    {
        static::$expiresAt = $minutes;
    }

    /**
     * Define a custom callback for generating the plain text token.
     */
    public static function plainTextTokenUsing(Closure $callback): void
    {
        static::$plainTextToken = $callback;
    }

    /**
     * Specify a custom personal token model class.
     */
    public static function usePersonalTokenModel(string $model): void
    {
        static::$personalTokenModel = $model;
    }

    /**
     * Resolve a new instance of the configured PersonalToken model.
     */
    public static function resolveModel(): PersonalToken
    {
        return resolve(static::$personalTokenModel);
    }

    /**
     * Proxy method to create a new personal token using the configured model.
     */
    public static function createToken(...$args): string
    {
        return static::resolveModel()::createToken(...$args);
    }

    /**
     * Create a new plain text token using the configured generator or default random string.
     */
    public static function createPlainTextToken(): string
    {
        if (blank(static::$plainTextToken)) {
            return Str::random(40);
        }

        return (static::$plainTextToken)();
    }
}
