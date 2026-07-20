<?php

declare(strict_types=1);

namespace App\Enum;

enum Course: string
{
    case STARTER = 'starter';
    case MAIN = 'main';
    case SIDE = 'side';
    case DESSERT = 'dessert';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
