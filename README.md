# 📌 Laravel Personal Tokens

This package provides support for generating and validating personal tokens in Laravel. Personal tokens are useful for temporary authentication, one-time access links, email confirmations, password resets, and other use cases that require secure and temporary access.

### 🧩 Features

- ✅ **Secure token generation**: Encrypted tokens with secure hashing
- ✅ **Automatic expiration**: Control over token lifetime
- ✅ **One-time use**: Tokens can be marked as used
- ✅ **Token types**: Support for different token types
- ✅ **Custom payload**: Storage of additional data
- ✅ **Integrated middleware**: Easy route protection
- ✅ **Model trait**: Simple Eloquent integration
- ✅ **Flexible configuration**: Complete behavior customization

### 📦 Installation

You can install the package via Composer:

```bash
composer require pijler/personal-tokens
```

### 🗄️ Publishing Migrations

Publish the package migrations:

```bash
php artisan vendor:publish --tag=personal-tokens-migrations
```

Run the migrations:

```bash
php artisan migrate
```

### ⚙️ Configuration

#### Basic Configuration

The package works out-of-the-box, but you can customize the behavior:

```php
use PersonalTokens\TokenCreator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Set default expiration time (in minutes)
        TokenCreator::expiresAt(60);

        // Use custom model
        TokenCreator::usePersonalTokenModel(CustomPersonalToken::class);

        // Customize token generation
        TokenCreator::plainTextTokenUsing(fn () => 'prefix-' . Str::random(32));
    }
}
```

### 🧠 Usage

#### 1. Using the HasTokens Trait

Add the `HasTokens` trait to your model:

```php
use PersonalTokens\Traits\HasTokens;

class User extends Model
{
    use HasTokens;
}
```

#### 2. Creating Tokens

```php
use Illuminate\Support\Carbon;

$user = User::find(1);

// Basic token
$token = $user->createToken('email-verification');

// Token with custom payload
$token = $user->createToken(
    type: 'password-reset',
    payload: [
        'ip' => request()->ip(),
        'email' => $user->email,
    ],
);

// Token with custom expiration
$token = $user->createToken(
    type: 'temporary-access',
    expiresAt: Carbon::now()->addDays(7),
);

// Token with custom value
$token = $user->createToken(
    type: 'custom-token',
    plainTextToken: 'my-custom-token',
);
```

#### 3. Validating Tokens

```php
use PersonalTokens\TokenCreator;

// Find token
$personalToken = TokenCreator::findToken($tokenString);

if ($personalToken && !$personalToken->isUsed() && !$personalToken->isExpired()) {
    // Valid token
    $owner = $personalToken->owner;
    $payload = $personalToken->payload;

    // Mark as used (optional)
    $personalToken->markAsUsed();
}
```

#### 4. Using the Middleware

The package includes middleware to automatically protect routes:

```php
// routes/web.php
use Illuminate\Support\Facades\Route;

// Route protected with any token
Route::post('/protected', function () {
    return response()->json(['message' => 'Access granted']);
})->middleware('personal-token');

// Route protected with specific token type
Route::post('/email-verify', function () {
    return response()->json(['message' => 'Email verified']);
})->middleware('personal-token:email-verification');
```

The middleware expects the token in the `token` parameter of the request:

```javascript
// Example usage with JavaScript
fetch("/protected", {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
    token: "your-token-here",
  }),
});
```

#### 5. Working with the PersonalToken Model

```php
use PersonalTokens\Models\PersonalToken;

// Create token directly
$token = PersonalToken::createToken(
    model: $user,
    type: 'email-verification',
    plainTextToken: 'custom-token',
    expiresAt: Carbon::now()->addHour(),
    payload: ['action' => 'verify-email'],
);

// Check token status
$personalToken = PersonalToken::find(1);

if ($personalToken->isUsed()) {
    // Token already used
}

if ($personalToken->isExpired()) {
    // Token expired
}

// Check if belongs to owner
if ($personalToken->belongsToOwner($user)) {
    // Token belongs to user
}
```

### 🧩 API Reference

#### TokenCreator

```php
// Configuration
TokenCreator::expiresAt(int $minutes): void
TokenCreator::usePersonalTokenModel(string $model): void
TokenCreator::plainTextTokenUsing(Closure $callback): void

// Methods
TokenCreator::findToken(string $token): ?PersonalToken
TokenCreator::createPlainTextToken(): string
TokenCreator::createToken(
    mixed $type,
    ?Model $model = null,
    ?array $payload = null,
    ?Carbon $expiresAt = null,
    ?string $plainTextToken = null,
): string
```

#### PersonalToken Model

```php
// Relationships
$token->owner(): MorphTo

// Verification methods
$token->isUsed(): bool
$token->isExpired(): bool
$token->belongsToOwner(Model $owner): bool

// Actions
$token->markAsUsed(): int

// Static methods
PersonalToken::createToken(...): string
PersonalToken::findToken(string $token): ?self
```

#### HasTokens Trait

```php
// Methods available on model
$model->tokens(): MorphMany
$model->currentToken(): ?PersonalToken
$model->withToken(?PersonalToken $token): self
$model->createToken(
    mixed $type,
    ?array $payload = null,
    ?Carbon $expiresAt = null,
    ?string $plainTextToken = null,
): string
```

### 📝 License

Open-source under the [MIT license](LICENSE).

## 🚀 Thanks!
