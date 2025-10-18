<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use PersonalTokens\TokenCreator;
use Workbench\App\Models\PersonalToken;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        TokenCreator::usePersonalTokenModel(PersonalToken::class);
    }
}
