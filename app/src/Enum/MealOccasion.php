<?php

declare(strict_types=1);

namespace App\Enum;

enum MealOccasion: string
{
    case BREAKFAST = 'breakfast';
    case BRUNCH = 'brunch';
    case LUNCH = 'lunch';
    case DINNER = 'dinner';
    case SUPPER = 'supper';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
