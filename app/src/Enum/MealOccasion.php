<?php

declare(strict_types=1);

namespace App\Enum;

enum MealOccasion: string
{
    case BREAKFAST = 'breakfast';
    case LUNCH = 'lunch';
    case DINNER = 'dinner';
    case BAKED_GOODS = 'baked_goods';
    case STAPLE = 'staple';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
