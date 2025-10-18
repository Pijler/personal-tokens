<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PersonalTokens\TokenCreator;
use Workbench\App\Enums\TokenType;
use Workbench\App\Models\PersonalToken;
use Workbench\App\Models\User;

beforeEach(function () {
    // Reset TokenCreator static properties
    TokenCreator::$expiresAt = 1440;
    TokenCreator::$plainTextToken = null;
    TokenCreator::$personalTokenModel = PersonalToken::class;
});

test('it should have correct guarded attributes', function () {
    $model = new PersonalToken;

    expect($model->getGuarded())->toBe([]);
});

test('it should have correct hidden attributes', function () {
    $model = new PersonalToken;

    expect($model->getHidden())->toBe(['token']);
});

test('it should have correct casts', function () {
    $model = new PersonalToken;
    $casts = $model->getCasts();

    expect($casts)->toHaveKey('token', 'hashed');
    expect($casts)->toHaveKey('used_at', 'datetime');
    expect($casts)->toHaveKey('expires_at', 'datetime');
    expect($casts)->toHaveKey('payload', 'encrypted:array');
});

test('it should return morphTo relationship for owner', function () {
    $model = new PersonalToken;
    $result = $model->owner();

    expect($result)->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class);
});

test('it should return true when token is used', function () {
    $model = PersonalToken::factory()->create([
        'used_at' => Carbon::now(),
    ]);

    expect($model->isUsed())->toBeTrue();
});

test('it should return false when token is not used', function () {
    $model = PersonalToken::factory()->create([
        'used_at' => null,
    ]);

    expect($model->isUsed())->toBeFalse();
});

test('it should return true when token is expired', function () {
    $model = PersonalToken::factory()->create([
        'expires_at' => Carbon::now()->subHour(),
    ]);

    expect($model->isExpired())->toBeTrue();
});

test('it should return false when token is not expired', function () {
    $model = PersonalToken::factory()->create([
        'expires_at' => Carbon::now()->addHour(),
    ]);

    expect($model->isExpired())->toBeFalse();
});

test('it should mark token as used', function () {
    $model = PersonalToken::factory()->create([
        'used_at' => null,
    ]);

    $result = $model->markAsUsed();

    expect($result)->toBe(1);
    expect($model->fresh()->isUsed())->toBeTrue();
});

test('it should find token with valid encrypted token', function () {
    $model = PersonalToken::factory()->create([
        'token' => $plainToken = Str::random(40),
    ]);

    $encryptedToken = encrypt("{$model->id}|{$plainToken}");

    $result = PersonalToken::findToken($encryptedToken);

    expect($result)->toBeInstanceOf(PersonalToken::class);
    expect($result->id)->toBe($model->id);
});

test('it should return null when token cannot be decrypted', function () {
    $invalidToken = 'invalid-encrypted-token';

    $result = PersonalToken::findToken($invalidToken);

    expect($result)->toBeNull();
});

test('it should return null when token hash does not match', function () {
    $model = PersonalToken::factory()->create([
        'token' => Hash::make('different-token'),
    ]);

    $encryptedToken = encrypt("{$model->id}|plain-token");

    $result = PersonalToken::findToken($encryptedToken);

    expect($result)->toBeNull();
});

test('it should return null when token model is not found', function () {
    $encryptedToken = encrypt('999|plain-token');

    $result = PersonalToken::findToken($encryptedToken);

    expect($result)->toBeNull();
});

test('it should create token with all parameters', function () {
    $type = 'test-type';
    $payload = ['key' => 'value'];
    $plainTextToken = 'custom-token';
    $expiresAt = Carbon::now()->addHour();
    $user = User::inRandomOrder()->first();

    $result = PersonalToken::createToken($type, $user, $payload, $expiresAt, $plainTextToken);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);

    // Verify the token can be decrypted
    $decrypted = decrypt($result);
    expect($decrypted)->toContain('|'.$plainTextToken);
});

test('it should create token with default parameters', function () {
    $type = 'test-type';

    $result = PersonalToken::createToken($type);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);

    // Verify the token can be decrypted
    $decrypted = decrypt($result);
    expect($decrypted)->toContain('|');
});

test('it should create token without model', function () {
    $type = 'test-type';

    $result = PersonalToken::createToken($type);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create token with custom plain text token', function () {
    $type = 'test-type';
    $plainTextToken = 'custom-plain-token';

    $result = PersonalToken::createToken($type, null, null, null, $plainTextToken);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);

    // Verify the token contains the custom plain text
    $decrypted = decrypt($result);
    expect($decrypted)->toEndWith($plainTextToken);
});

test('it should create token with custom expiration time', function () {
    $type = 'test-type';
    $expiresAt = Carbon::now()->addDays(7);

    $result = PersonalToken::createToken($type, null, null, $expiresAt);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create token with payload', function () {
    $type = 'test-type';
    $payload = ['user_id' => 123, 'permissions' => ['read', 'write']];

    $result = PersonalToken::createToken($type, null, $payload);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create token with complex payload', function () {
    $type = 'test-type';
    $payload = [
        'user_id' => 123,
        'permissions' => ['read', 'write', 'admin'],
        'metadata' => [
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'created_at' => Carbon::now()->toISOString(),
        ],
    ];

    $result = PersonalToken::createToken($type, null, $payload);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create token with different types', function () {
    $types = ['invite_user', 'new_device', 'password_reset', 'email_verification'];

    foreach ($types as $type) {
        $result = PersonalToken::createToken($type);

        expect($result)->toBeString();
        expect(strlen($result))->toBeGreaterThan(0);
    }
});

test('it should create token with future expiration', function () {
    $type = 'test-type';
    $expiresAt = Carbon::now()->addYear();

    $result = PersonalToken::createToken($type, null, null, $expiresAt);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create token with past expiration', function () {
    $type = 'test-type';
    $expiresAt = Carbon::now()->subHour();

    $result = PersonalToken::createToken($type, null, null, $expiresAt);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create token with very long plain text', function () {
    $type = 'test-type';
    $plainTextToken = Str::random(1000);

    $result = PersonalToken::createToken($type, null, null, null, $plainTextToken);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);

    // Verify the token contains the long plain text
    $decrypted = decrypt($result);
    expect($decrypted)->toEndWith($plainTextToken);
});

test('it should create token with empty plain text', function () {
    $type = 'test-type';
    $plainTextToken = '';

    $result = PersonalToken::createToken($type, null, null, null, $plainTextToken);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);

    // Verify the token contains the empty plain text
    $decrypted = decrypt($result);
    expect($decrypted)->toEndWith('|'.$plainTextToken);
});

test('it should create multiple tokens with different values', function () {
    $type = 'test-type';

    $result1 = PersonalToken::createToken($type);
    $result2 = PersonalToken::createToken($type);

    expect($result1)->toBeString();
    expect($result2)->toBeString();
    expect($result1)->not->toBe($result2);
});

test('it should create token with model association', function () {
    $user = User::inRandomOrder()->first();

    $type = 'test-type';

    $result = PersonalToken::createToken($type, $user);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);

    // Verify the token can be decrypted
    $decrypted = decrypt($result);
    expect($decrypted)->toContain('|');
});

test('it should handle enum type conversion', function () {
    $enumType = TokenType::TYPE_1;

    $result = PersonalToken::createToken($enumType);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should handle string type', function () {
    $type = 'string-type';

    $result = PersonalToken::createToken($type);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should handle null type', function () {
    $result = PersonalToken::createToken('');

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should handle empty string type', function () {
    $result = PersonalToken::createToken('');

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should return true when token belongs to the given owner', function () {
    $owner = User::inRandomOrder()->first();

    $token = PersonalToken::factory()->owner($owner)->create();

    expect($token->belongsToOwner($owner))->toBeTrue();
});

test('it should return false when token does not belong to the given owner', function () {
    $owner1 = User::inRandomOrder()->first();
    $owner2 = User::where('id', '!=', $owner1->id)->inRandomOrder()->first();

    $token = PersonalToken::factory()->owner($owner1)->create();

    expect($token->belongsToOwner($owner2))->toBeFalse();
});

test('it should return false when token belongs to different owner type', function () {
    $owner1 = User::inRandomOrder()->first();

    // Create a different model class for testing different owner type
    $owner2 = new class extends Model
    {
        public $id = 1;

        protected $table = 'different_models';
    };

    $token = PersonalToken::factory()->owner($owner1)->create();

    expect($token->belongsToOwner($owner2))->toBeFalse();
});

test('it should return false when token belongs to different owner id', function () {
    $owner1 = User::inRandomOrder()->first();
    $owner2 = User::where('id', '!=', $owner1->id)->inRandomOrder()->first();

    $token = PersonalToken::factory()->owner($owner1)->create();

    expect($token->belongsToOwner($owner2))->toBeFalse();
});

test('it should return false when token has no owner', function () {
    $owner = User::inRandomOrder()->first();

    $token = PersonalToken::factory()->create();

    expect($token->belongsToOwner($owner))->toBeFalse();
});
