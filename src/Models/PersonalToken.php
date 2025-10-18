<?php

namespace PersonalTokens\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use PersonalTokens\TokenCreator;

use function Illuminate\Support\enum_value;

class PersonalToken extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'token' => 'hashed',
            'used_at' => 'datetime',
            'expires_at' => 'datetime',
            'payload' => 'encrypted:array',
        ];
    }

    /**
     * Get the owner model that the personal token belongs to.
     */
    public function owner(): MorphTo
    {
        return $this->morphTo('owner');
    }

    /**
     * Check if the personal token is used.
     */
    public function isUsed(): bool
    {
        return filled($this->used_at);
    }

    /**
     * Check if the personal token is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Mark the token as used.
     */
    public function markAsUsed(): int
    {
        return $this->update(['used_at' => Carbon::now()]);
    }

    /**
     * Check if the token belongs to the given owner.
     */
    public function belongsToOwner(Model $owner): bool
    {
        return $this->owner_id === $owner->getKey()
            && $this->owner_type === get_class($owner);
    }

    /**
     * Find the token instance matching the given token.
     */
    public static function findToken(string $token): ?self
    {
        return rescue(function () use ($token) {
            [$id, $token] = explode('|', decrypt($token), 2);

            $instance = self::find($id);

            return Hash::check($token, $instance?->token) ? $instance : null;
        }, report: false);
    }

    /**
     * Create a new personal token.
     */
    public static function createToken(
        mixed $type,
        ?Model $model = null,
        ?array $payload = null,
        ?Carbon $expiresAt = null,
        ?string $plainTextToken = null,
    ): string {
        $plainTextToken ??= TokenCreator::createPlainTextToken();

        $expiresAt ??= Carbon::now()->addMinutes(TokenCreator::$expiresAt);

        $token = self::make([
            'payload' => $payload,
            'token' => $plainTextToken,
            'expires_at' => $expiresAt,
            'type' => enum_value($type),
        ]);

        $token->owner()->associate($model)->save();

        return encrypt("{$token->id}|{$plainTextToken}");
    }
}
