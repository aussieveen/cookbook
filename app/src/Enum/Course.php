<?php

declare(strict_types=1);

namespace App\Enum;

enum Course: string
{
    case BREAKFAST = 'breakfast';
    case STARTER = 'starter';
    case MAIN = 'main';
    case SIDE = 'side';
    case SALAD = 'salad';
    case SOUP = 'soup';
    case DESSERT = 'dessert';
    case SNACK = 'snack';

    public function label(): string
    {
        return match($this) {
            Course::BREAKFAST => 'Breakfast',
            Course::STARTER => 'Starter',
            Course::MAIN => 'Main',
            Course::SIDE => 'Side',
            Course::SALAD => 'Salad',
            Course::SOUP => 'Soup',
            Course::DESSERT => 'Dessert',
            Course::SNACK => 'Snack',
        };
    }
}
