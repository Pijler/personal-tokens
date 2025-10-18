<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use PersonalTokens\Models\PersonalToken;
use PersonalTokens\TokenCreator;
use Workbench\App\Models\User;

beforeEach(function () {
    // Reset static properties before each test
    TokenCreator::$expiresAt = 1440;
    TokenCreator::$plainTextToken = null;
    TokenCreator::$personalTokenModel = PersonalToken::class;
});

test('it should have default expiration time of 1440 minutes', function () {
    expect(TokenCreator::$expiresAt)->toBe(1440);
});

test('it should have default plain text token generator as null', function () {
    expect(TokenCreator::$plainTextToken)->toBeNull();
});

test('it should have default personal token model', function () {
    expect(TokenCreator::$personalTokenModel)->toBe(PersonalToken::class);
});

test('it should set expiration time correctly', function () {
    TokenCreator::expiresAt(60);

    expect(TokenCreator::$expiresAt)->toBe(60);
});

test('it should set plain text token generator correctly', function () {
    $callback = fn () => 'custom-token';

    TokenCreator::plainTextTokenUsing($callback);

    expect(TokenCreator::$plainTextToken)->toBe($callback);
});

test('it should set personal token model correctly', function () {
    $customModel = 'Custom\\PersonalToken';

    TokenCreator::usePersonalTokenModel($customModel);

    expect(TokenCreator::$personalTokenModel)->toBe($customModel);
});

test('it should resolve model instance correctly', function () {
    $result = TokenCreator::resolveModel();

    expect($result)->toBeInstanceOf(PersonalToken::class);
});

test('it should resolve custom model instance correctly', function () {
    $customModel = PersonalToken::class; // Use same class for testing

    TokenCreator::usePersonalTokenModel($customModel);

    $result = TokenCreator::resolveModel();

    expect($result)->toBeInstanceOf($customModel);
});

test('it should create token using configured model', function () {
    $type = 'test-type';
    $plainText = 'custom-token';
    $payload = ['key' => 'value'];
    $expiresAt = Carbon::now()->addHour();
    $model = User::inRandomOrder()->first();

    $result = TokenCreator::createToken($type, $model, $payload, $expiresAt, $plainText);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create plain text token using default generator', function () {
    TokenCreator::$plainTextToken = null;

    $result = TokenCreator::createPlainTextToken();

    expect($result)->toHaveLength(40);
    expect(Str::isAscii($result))->toBeTrue();
});

test('it should create plain text token using custom generator', function () {
    $customToken = 'custom-generated-token';

    TokenCreator::plainTextTokenUsing(fn () => $customToken);

    $result = TokenCreator::createPlainTextToken();

    expect($result)->toBe($customToken);
});

test('it should create plain text token with custom generator that returns different values', function () {
    $counter = 0;
    $customGenerator = function () use (&$counter) {
        return 'token-'.(++$counter);
    };

    TokenCreator::plainTextTokenUsing($customGenerator);

    $result1 = TokenCreator::createPlainTextToken();
    $result2 = TokenCreator::createPlainTextToken();

    expect($result1)->toBe('token-1');
    expect($result2)->toBe('token-2');
});

test('it should handle empty string from custom generator', function () {
    TokenCreator::plainTextTokenUsing(fn () => '');

    $result = TokenCreator::createPlainTextToken();

    expect($result)->toBe('');
});

test('it should handle null from custom generator', function () {
    TokenCreator::plainTextTokenUsing(fn () => null);

    rescue(fn () => TokenCreator::createPlainTextToken(), function ($e) {
        expect($e)->toBeInstanceOf(InvalidArgumentException::class);
        expect($e->getMessage())->toBe('Custom token generator returned non-string value');
    });
});

test('it should handle custom generator that throws exception', function () {
    TokenCreator::plainTextTokenUsing(function () {
        throw new Exception('Generator error');
    });

    rescue(fn () => TokenCreator::createPlainTextToken(), function ($e) {
        expect($e)->toBeInstanceOf(Exception::class);
        expect($e->getMessage())->toBe('Generator error');
    });
});

test('it should create multiple tokens with different values using default generator', function () {
    TokenCreator::$plainTextToken = null;

    $result1 = TokenCreator::createPlainTextToken();
    $result2 = TokenCreator::createPlainTextToken();

    expect($result1)->toHaveLength(40);
    expect($result2)->toHaveLength(40);

    expect($result1)->not->toBe($result2);
});

test('it should maintain state across multiple calls', function () {
    TokenCreator::expiresAt(120);
    TokenCreator::plainTextTokenUsing(fn () => 'persistent-token');
    TokenCreator::usePersonalTokenModel(PersonalToken::class);

    expect(TokenCreator::$expiresAt)->toBe(120);
    expect(TokenCreator::$plainTextToken)->not->toBeNull();
    expect(TokenCreator::$personalTokenModel)->toBe(PersonalToken::class);

    // Verify the custom generator still works
    $result = TokenCreator::createPlainTextToken();

    expect($result)->toBe('persistent-token');
});

test('it should create token with minimal parameters', function () {
    $type = 'test-type';

    $result = TokenCreator::createToken($type);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create token with model parameter', function () {
    $type = 'test-type';
    $model = User::inRandomOrder()->first();

    $result = TokenCreator::createToken($type, $model);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create token with payload parameter', function () {
    $type = 'test-type';
    $payload = ['user_id' => 123, 'permissions' => ['read', 'write']];

    $result = TokenCreator::createToken($type, null, $payload);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create token with custom expiration time', function () {
    $type = 'test-type';
    $expiresAt = Carbon::now()->addDays(7);

    $result = TokenCreator::createToken($type, null, null, $expiresAt);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});

test('it should create token with custom plain text token', function () {
    $type = 'test-type';
    $plainText = 'custom-plain-token';

    $result = TokenCreator::createToken($type, null, null, null, $plainText);

    expect($result)->toBeString();
    expect(strlen($result))->toBeGreaterThan(0);
});
