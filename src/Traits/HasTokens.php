<?php

namespace PersonalTokens\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use PersonalTokens\Models\PersonalToken;
use PersonalTokens\TokenCreator;

trait HasTokens
{
    /**
     * The personal token the model is using for the current request.
     */
    private ?PersonalToken $token = null;

    /**
     * Get the personal token currently associated with the model.
     */
    public function currentToken(): ?PersonalToken
    {
        return $this->token;
    }

    /**
     * Set the current personal token for the model.
     */
    public function withToken(PersonalToken $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get the personal tokens that belong to model.
     */
    public function tokens(): MorphMany
    {
        $modelClass = TokenCreator::$personalTokenModel;

        return $this->morphMany($modelClass, 'owner');
    }

    /**
     * Create a new personal token for the model.
     */
    public function createToken(
        mixed $type,
        ?array $payload = null,
        ?Carbon $expiresAt = null,
        ?string $plainTextToken = null,
    ): string {
        return TokenCreator::createToken(
            type: $type,
            model: $this,
            payload: $payload,
            expiresAt: $expiresAt,
            plainTextToken: $plainTextToken,
        );
    }
}
