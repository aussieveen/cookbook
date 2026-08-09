<?php

declare(strict_types=1);

namespace App\Enum;

enum RecipeType: string
{
    case RECIPE = 'recipe';
    case ITEM   = 'item';
}
