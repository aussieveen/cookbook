<?php

declare(strict_types=1);

namespace App\Enum;

enum RecipeCategory: string
{
    case BREAKFAST = 'breakfast';
    case LUNCH = 'lunch';
    case DINNER = 'dinner';
    case BAKED_GOODS = 'baked_goods';
    case STAPLE = 'staple';
    case SAUCES_AND_MARINADES = 'sauces_and_marinades';
    case FOR_LEO = 'for_leo';

    public function label(): string
    {
        return match ($this) {
            self::SAUCES_AND_MARINADES => 'Sauces & Marinades',
            self::FOR_LEO              => 'For Leo',
            default                    => ucwords(str_replace('_', ' ', $this->value)),
        };
    }
}
