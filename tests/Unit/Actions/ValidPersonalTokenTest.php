<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use PersonalTokens\Actions\ValidPersonalToken;
use Workbench\App\Models\PersonalToken;

test('it should return null when token is not found', function () {
    $result = ValidPersonalToken::handle('invalid-token');

    expect($result)->toBeNull();
});

test('it should return null when token is used', function () {
    $personal = PersonalToken::factory()->create([
        'used_at' => Carbon::now(),
        'token' => $token = Str::random(40),
    ]);

    $plainToken = encrypt("{$personal->id}|{$token}");

    $result = ValidPersonalToken::handle($plainToken);

    expect($result)->toBeNull();
});

test('it should return null when token is expired', function () {
    $personal = PersonalToken::factory()->create([
        'used_at' => null,
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->subHour(),
    ]);

    $plainToken = encrypt("{$personal->id}|{$token}");

    $result = ValidPersonalToken::handle($plainToken);

    expect($result)->toBeNull();
});

test('it should return null when token type does not match', function () {
    $personal = PersonalToken::factory()->create([
        'used_at' => null,
        'type' => 'type-1',
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->addHour(),
    ]);

    $plainToken = encrypt("{$personal->id}|{$token}");

    $result = ValidPersonalToken::handle($plainToken, 'type-2');

    expect($result)->toBeNull();
});

test('it should return PersonalToken when token is valid without type check', function () {
    $personal = PersonalToken::factory()->create([
        'used_at' => null,
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->addHour(),
    ]);

    $plainToken = encrypt("{$personal->id}|{$token}");

    $result = ValidPersonalToken::handle($plainToken);

    expect($result->id)->toBe($personal->id);
    expect($result)->toBeInstanceOf(PersonalToken::class);
});

test('it should return PersonalToken when token is valid with matching type', function () {
    $personal = PersonalToken::factory()->create([
        'used_at' => null,
        'type' => 'type-1',
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->addHour(),
    ]);

    $plainToken = encrypt("{$personal->id}|{$token}");

    $result = ValidPersonalToken::handle($plainToken, 'type-1');

    expect($result->id)->toBe($personal->id);
    expect($result)->toBeInstanceOf(PersonalToken::class);
});

test('it should return null when token is used even if type matches', function () {
    $personal = PersonalToken::factory()->create([
        'type' => 'type-1',
        'used_at' => Carbon::now(),
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->addHour(),
    ]);

    $plainToken = encrypt("{$personal->id}|{$token}");

    $result = ValidPersonalToken::handle($plainToken, 'type-1');

    expect($result)->toBeNull();
});

test('it should return null when token is expired even if type matches', function () {
    $personal = PersonalToken::factory()->create([
        'used_at' => null,
        'type' => 'type-1',
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->subHour(),
    ]);

    $plainToken = encrypt("{$personal->id}|{$token}");

    $result = ValidPersonalToken::handle($plainToken, 'type-1');

    expect($result)->toBeNull();
});

test('it should handle null type parameter correctly', function () {
    $personal = PersonalToken::factory()->create([
        'used_at' => null,
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->addHour(),
    ]);

    $plainToken = encrypt("{$personal->id}|{$token}");

    $result = ValidPersonalToken::handle($plainToken, null);

    expect($result->id)->toBe($personal->id);
    expect($result)->toBeInstanceOf(PersonalToken::class);
});

test('it should handle empty string type parameter correctly', function () {
    $personal = PersonalToken::factory()->create([
        'type' => '',
        'used_at' => null,
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->addHour(),
    ]);

    $plainToken = encrypt("{$personal->id}|{$token}");

    $result = ValidPersonalToken::handle($plainToken, '');

    expect($result->id)->toBe($personal->id);
    expect($result)->toBeInstanceOf(PersonalToken::class);
});
