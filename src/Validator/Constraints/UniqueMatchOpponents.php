<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueMatchOpponents extends Constraint
{
    public string $message = 'Ces deux joueurs se sont déjà affrontés dans ce tournoi.';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
