<?php

namespace Pijler\PersonalTokens\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Pijler\PersonalTokens\Models\PersonalToken;
use Pijler\PersonalTokens\TokenCreator;

trait HasTokens
{
    /**
     * The personal token the model is using for the current request.
     */
    private ?PersonalToken $personalToken = null;

    /**
     * Get the personal token currently associated with the model.
     */
    public function currentPersonalToken(): ?PersonalToken
    {
        return $this->personalToken;
    }

    /**
     * Set the current personal token for the model.
     */
    public function withPersonalToken(?PersonalToken $token): self
    {
        $this->personalToken = $token;

        return $this;
    }

    /**
     * Get the personal tokens that belong to model.
     */
    public function personalTokens(): MorphMany
    {
        $modelClass = TokenCreator::$personalTokenModel;

        return $this->morphMany($modelClass, 'owner');
    }

    /**
     * Create a new personal token for the model.
     */
    public function createPersonalToken(
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
