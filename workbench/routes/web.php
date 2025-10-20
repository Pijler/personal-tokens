<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use PersonalTokens\Actions\ValidPersonalToken;
use Workbench\App\Models\User;

Route::get('/protected', function () {
    return response()->json(['message' => 'Access granted']);
})->middleware('personal-token');

Route::get('/protected-typed', function () {
    return response()->json(['message' => 'Typed access granted']);
})->middleware('personal-token:test-type');

Route::post('/tokens', function () {
    /** @var User $user */
    $user = Auth::user();

    $token = $user->createToken(
        type: 'test-type',
        payload: ['test' => 'data'],
    );

    return response()->json([
        'token' => $token,
        'user_id' => $user->id,
    ]);
});

Route::post('/tokens/custom', function () {
    /** @var User $user */
    $user = Auth::user();

    $token = $user->createToken(
        type: 'custom-type',
        plainTextToken: '123456',
        payload: ['custom' => 'payload'],
        expiresAt: Carbon::now()->addHours(2),
    );

    return response()->json([
        'token' => $token,
        'user_id' => $user->id,
    ]);
});

Route::post('/tokens/use', function () {
    /** @var User $user */
    $user = Auth::user();

    $personalToken = $user->tokens()->whereNull('used_at')->first();

    if (blank($personalToken)) {
        return response()->json([
            'error' => 'No unused token found',
        ], 404);
    }

    $personalToken->markAsUsed();

    return response()->json([
        'token_id' => $personalToken->id,
        'message' => 'Token marked as used',
    ]);
});

Route::get('/tokens/validate', function () {
    $token = request()->input('token');

    if (blank($token)) {
        return response()->json([
            'valid' => false,
            'error' => 'No token provided',
        ], 400);
    }

    $personalToken = ValidPersonalToken::handle($token);

    return response()->json([
        'token' => $personalToken,
        'valid' => filled($personalToken),
    ]);
});

Route::get('/tokens/validate-typed', function () {
    $token = request()->input('token');
    $type = request()->input('type', 'test-type');

    if (blank($token)) {
        return response()->json([
            'valid' => false,
            'error' => 'No token provided',
        ], 400);
    }

    $personalToken = ValidPersonalToken::handle($token, $type);

    return response()->json([
        'type' => $type,
        'token' => $personalToken,
        'valid' => filled($personalToken),
    ]);
});
