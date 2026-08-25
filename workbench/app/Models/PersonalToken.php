<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Pijler\PersonalTokens\Models\PersonalToken as BasePersonalToken;

class PersonalToken extends BasePersonalToken
{
    use HasFactory;
}
