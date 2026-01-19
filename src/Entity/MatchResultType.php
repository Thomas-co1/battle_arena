<?php

namespace App\Entity;

enum MatchResultType: string
{
    case WIN = 'win';
    case LOSS = 'loss';
    case DRAW = 'draw';

    public function getLabel(): string
    {
        return match ($this) {
            MatchResultType::WIN => 'Victoire',
            MatchResultType::LOSS => 'Défaite',
            MatchResultType::DRAW => 'Égalité',
        };
    }
}
