<?php

declare(strict_types=1);

namespace App\Enum;

enum IngredientCategory: string
{
    case PRODUCE           = 'produce';
    case MEAT_AND_FISH     = 'meat_and_fish';
    case DAIRY_AND_CHILLED = 'dairy_and_chilled';
    case STORE_CUPBOARD    = 'store_cupboard';

    public function label(): string
    {
        return match($this) {
            self::PRODUCE           => 'Produce',
            self::MEAT_AND_FISH     => 'Meat & Fish',
            self::DAIRY_AND_CHILLED => 'Dairy & Chilled',
            self::STORE_CUPBOARD    => 'Store Cupboard',
        };
    }
}
