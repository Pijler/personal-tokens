<?php

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use PersonalTokens\TokenCreator;
use Workbench\App\Models\PersonalToken;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->model = User::inRandomOrder()->first();

    // Reset TokenCreator static properties
    TokenCreator::$expiresAt = 1440;
    TokenCreator::$plainTextToken = null;
    TokenCreator::$personalTokenModel = PersonalToken::class;
});

test('it should initialize with null current token', function () {
    expect($this->model->currentToken())->toBeNull();
});

test('it should set and get current token', function () {
    $token = PersonalToken::factory()->create();

    $result = $this->model->withToken($token);

    expect($result)->toBe($this->model);
    expect($this->model->currentToken())->toBe($token);
});

test('it should return morphMany relationship for tokens', function () {
    $result = $this->model->tokens();

    expect($result)->toBeInstanceOf(MorphMany::class);
});

test('it should use custom personal token model for tokens relationship', function () {
    $customModel = PersonalToken::class; // Use same class for testing
    TokenCreator::usePersonalTokenModel($customModel);

    $result = $this->model->tokens();

    expect($result)->toBeInstanceOf(MorphMany::class);
});

test('it should create token with all parameters', function () {
    $type = 'test-type';
    $payload = ['key' => 'value'];
    $plainTextToken = 'plain-token';
    $expiresAt = Carbon::now()->addHour();

    $result = $this->model->createToken($type, $payload, $expiresAt, $plainTextToken);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create token with minimal parameters', function () {
    $type = 'test-type';

    $result = $this->model->createToken($type);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create token with null payload', function () {
    $type = 'test-type';

    $result = $this->model->createToken($type, null);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create token with empty array payload', function () {
    $type = 'test-type';
    $payload = [];

    $result = $this->model->createToken($type, $payload);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create token with custom expiration time', function () {
    $type = 'test-type';
    $expiresAt = Carbon::now()->addDays(7);

    $result = $this->model->createToken($type, null, $expiresAt);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create token with custom plain text token', function () {
    $type = 'test-type';
    $plainTextToken = 'custom-plain-token';

    $result = $this->model->createToken($type, null, null, $plainTextToken);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create multiple tokens for the same model', function () {
    $type = 'test-type';

    $result1 = $this->model->createToken($type);
    $result2 = $this->model->createToken($type);

    expect($result1)->toBeString();
    expect($result2)->toBeString();
    expect($result1)->not->toBe($result2);
});

test('it should maintain current token state independently', function () {
    $token1 = PersonalToken::factory()->create();
    $token2 = PersonalToken::factory()->create();

    $this->model->withToken($token1);
    expect($this->model->currentToken())->toBe($token1);

    $this->model->withToken($token2);
    expect($this->model->currentToken())->toBe($token2);
});

test('it should handle null token assignment', function () {
    $this->model->withToken(null);
    expect($this->model->currentToken())->toBeNull();
});

test('it should use custom personal token model for createToken', function () {
    $customModel = PersonalToken::class; // Use same class for testing
    TokenCreator::usePersonalTokenModel($customModel);

    $type = 'test-type';

    $result = $this->model->createToken($type);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create token with different types', function () {
    $types = ['invite_user', 'new_device', 'password_reset', 'email_verification'];

    foreach ($types as $type) {
        $result = $this->model->createToken($type);

        expect($result)->toBeString();
        expect(strlen($result))->toBeGreaterThan(0);
    }
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

    $result = $this->model->createToken($type, $payload);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create token with future expiration', function () {
    $type = 'test-type';
    $expiresAt = Carbon::now()->addYear();

    $result = $this->model->createToken($type, null, $expiresAt);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create token with past expiration', function () {
    $type = 'test-type';
    $expiresAt = Carbon::now()->subHour();

    $result = $this->model->createToken($type, null, $expiresAt);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create token with very long plain text', function () {
    $type = 'test-type';
    $plainTextToken = Str::random(1000);

    $result = $this->model->createToken($type, null, null, $plainTextToken);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create token with empty plain text', function () {
    $type = 'test-type';
    $plainTextToken = '';

    $result = $this->model->createToken($type, null, null, $plainTextToken);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});
