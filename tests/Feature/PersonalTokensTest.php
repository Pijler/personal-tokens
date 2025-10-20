<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Workbench\App\Models\PersonalToken;

test('it should access protected route with valid token', function () {
    $personal = PersonalToken::factory()->create([
        'used_at' => null,
        'type' => 'test-type',
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->addHour(),
    ]);

    $token = encrypt("{$personal->id}|{$token}");

    $response = $this->postJson('/protected', ['token' => $token]);

    $response->assertOk();
    $response->assertJson(['message' => 'Access granted']);
});

test('it should access protected route without token', function () {
    $response = $this->postJson('/protected');

    $response->assertUnauthorized();
    $response->assertJson(['message' => 'Invalid or expired personal token.']);
});

test('it should access protected route with invalid token', function () {
    $response = $this->postJson('/protected', ['token' => 'invalid-token']);

    $response->assertUnauthorized();
    $response->assertJson(['message' => 'Invalid or expired personal token.']);
});

test('it should access protected route with used token', function () {
    $personal = PersonalToken::factory()->create([
        'type' => 'test-type',
        'used_at' => Carbon::now(),
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->addHour(),
    ]);

    $token = encrypt("{$personal->id}|{$token}");

    $response = $this->postJson('/protected', ['token' => $token]);

    $response->assertUnauthorized();
    $response->assertJson(['message' => 'Invalid or expired personal token.']);
});

test('it should access protected route with expired token', function () {
    $personal = PersonalToken::factory()->create([
        'used_at' => null,
        'type' => 'test-type',
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->subHour(),
    ]);

    $token = encrypt("{$personal->id}|{$token}");

    $response = $this->postJson('/protected', ['token' => $token]);

    $response->assertUnauthorized();
    $response->assertJson(['message' => 'Invalid or expired personal token.']);
});

test('it should access protected typed route with valid token', function () {
    $personal = PersonalToken::factory()->create([
        'used_at' => null,
        'type' => 'test-type',
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->addHour(),
    ]);

    $token = encrypt("{$personal->id}|{$token}");

    $response = $this->postJson('/protected-typed', ['token' => $token]);

    $response->assertOk();
    $response->assertJson(['message' => 'Typed access granted']);
});

test('it should access protected typed route without token', function () {
    $response = $this->postJson('/protected-typed');

    $response->assertUnauthorized();
    $response->assertJson(['message' => 'Invalid or expired personal token.']);
});

test('it should access protected typed route with invalid token', function () {
    $response = $this->postJson('/protected-typed', ['token' => 'invalid-token']);

    $response->assertUnauthorized();
    $response->assertJson(['message' => 'Invalid or expired personal token.']);
});

test('it should access protected typed route with used token', function () {
    $personal = PersonalToken::factory()->create([
        'type' => 'test-type',
        'used_at' => Carbon::now(),
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->addHour(),
    ]);

    $token = encrypt("{$personal->id}|{$token}");

    $response = $this->postJson('/protected-typed', ['token' => $token]);

    $response->assertUnauthorized();
    $response->assertJson(['message' => 'Invalid or expired personal token.']);
});

test('it should access protected typed route with expired token', function () {
    $personal = PersonalToken::factory()->create([
        'used_at' => null,
        'type' => 'test-type',
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->subHour(),
    ]);

    $token = encrypt("{$personal->id}|{$token}");

    $response = $this->postJson('/protected-typed', ['token' => $token]);

    $response->assertUnauthorized();
    $response->assertJson(['message' => 'Invalid or expired personal token.']);
});

test('it should access protected typed route with invalid type', function () {
    $personal = PersonalToken::factory()->create([
        'used_at' => null,
        'type' => 'other-type',
        'token' => $token = Str::random(40),
        'expires_at' => Carbon::now()->addHour(),
    ]);

    $token = encrypt("{$personal->id}|{$token}");

    $response = $this->postJson('/protected-typed', ['token' => $token]);

    $response->assertUnauthorized();
    $response->assertJson(['message' => 'Invalid or expired personal token.']);
});
