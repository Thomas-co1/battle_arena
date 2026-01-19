<?php

namespace App\Validator\Constraints;

use App\Entity\MatchResult;
use App\Repository\MatchResultRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueMatchOpponentsValidator extends ConstraintValidator
{
    public function __construct(private MatchResultRepository $matchRepository)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueMatchOpponents) {
            throw new UnexpectedTypeException($constraint, UniqueMatchOpponents::class);
        }

        if (!$value instanceof MatchResult) {
            throw new UnexpectedTypeException($value, MatchResult::class);
        }

        if ($value->getPlayer1() === null || $value->getPlayer2() === null || $value->getTournament() === null) {
            return;
        }

        // Check if the same players have already played in this tournament
        $existingMatch = $this->matchRepository->findExistingMatch(
            $value->getTournament(),
            $value->getPlayer1(),
            $value->getPlayer2(),
            $value->getId() // Exclude current match if updating
        );

        if ($existingMatch !== null) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
