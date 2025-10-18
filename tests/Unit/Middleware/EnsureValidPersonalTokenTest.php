<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use PersonalTokens\Middleware\EnsureValidPersonalToken;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Workbench\App\Models\PersonalToken;

beforeEach(function () {
    $this->request = new Request;

    $this->middleware = new EnsureValidPersonalToken;

    $this->next = fn ($request) => new Response('OK', 200);
});

test('it should abort with 401 when token is invalid', function () {
    $this->request->merge(['token' => 'invalid-token']);

    $this->expectException(HttpException::class);
    $this->expectExceptionMessage('Invalid or expired personal token.');

    $this->middleware->handle($this->request, $this->next);
});

test('it should abort with 401 when token is not provided', function () {
    $this->expectException(HttpException::class);
    $this->expectExceptionMessage('Invalid or expired personal token.');

    $this->middleware->handle($this->request, $this->next);
});

test('it should proceed to next middleware when token is valid', function () {
    $personal = PersonalToken::factory()->create([
        'used_at' => null,
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->addHour(),
    ]);

    $plainToken = encrypt("{$personal->id}|{$token}");
    $this->request->merge(['token' => $plainToken]);

    $result = $this->middleware->handle($this->request, $this->next);

    expect($result->getContent())->toBe('OK');
    expect($result->getStatusCode())->toBe(200);
    expect($result)->toBeInstanceOf(Response::class);
});

test('it should validate token with specific type when provided', function () {
    $personal = PersonalToken::factory()->create([
        'used_at' => null,
        'type' => 'invite_user',
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->addHour(),
    ]);

    $plainToken = encrypt("{$personal->id}|{$token}");
    $this->request->merge(['token' => $plainToken]);

    $result = $this->middleware->handle($this->request, $this->next, 'invite_user');

    expect($result->getContent())->toBe('OK');
    expect($result->getStatusCode())->toBe(200);
    expect($result)->toBeInstanceOf(Response::class);
});

test('it should abort with 401 when token type does not match', function () {
    $personal = PersonalToken::factory()->create([
        'used_at' => null,
        'type' => 'invite_user',
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->addHour(),
    ]);

    $plainToken = encrypt("{$personal->id}|{$token}");

    $this->request->merge(['token' => $plainToken]);

    $this->expectException(HttpException::class);
    $this->expectExceptionMessage('Invalid or expired personal token.');

    $this->middleware->handle($this->request, $this->next, 'new_device');
});

test('it should abort with 401 when token is used', function () {
    $personal = PersonalToken::factory()->create([
        'used_at' => Carbon::now(),
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->addHour(),
    ]);

    $plainToken = encrypt("{$personal->id}|{$token}");

    $this->request->merge(['token' => $plainToken]);

    $this->expectException(HttpException::class);
    $this->expectExceptionMessage('Invalid or expired personal token.');

    $this->middleware->handle($this->request, $this->next);
});

test('it should abort with 401 when token is expired', function () {
    $personal = PersonalToken::factory()->create([
        'used_at' => null,
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->subHour(),
    ]);

    $plainToken = encrypt("{$personal->id}|{$token}");

    $this->request->merge(['token' => $plainToken]);

    $this->expectException(HttpException::class);
    $this->expectExceptionMessage('Invalid or expired personal token.');

    $this->middleware->handle($this->request, $this->next);
});

test('it should handle empty string token as invalid', function () {
    $this->request->merge(['token' => '']);

    $this->expectException(HttpException::class);
    $this->expectExceptionMessage('Invalid or expired personal token.');

    $this->middleware->handle($this->request, $this->next);
});

test('it should handle false token as invalid', function () {
    $this->request->merge(['token' => false]);

    $this->expectException(HttpException::class);
    $this->expectExceptionMessage('Invalid or expired personal token.');

    $this->middleware->handle($this->request, $this->next);
});

test('it should handle array token as invalid', function () {
    $this->request->merge(['token' => ['token1', 'token2']]);

    $this->expectException(HttpException::class);
    $this->expectExceptionMessage('Invalid or expired personal token.');

    $this->middleware->handle($this->request, $this->next);
});

test('it should handle null type parameter correctly', function () {
    $personal = PersonalToken::factory()->create([
        'used_at' => null,
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->addHour(),
    ]);

    $plainToken = encrypt("{$personal->id}|{$token}");

    $this->request->merge(['token' => $plainToken]);

    $result = $this->middleware->handle($this->request, $this->next, null);

    expect($result->getContent())->toBe('OK');
    expect($result->getStatusCode())->toBe(200);
    expect($result)->toBeInstanceOf(Response::class);
});
