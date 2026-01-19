<?php

namespace App\Message;

class GenerateTournamentPdf
{
    public function __construct(
        private int $tournamentId,
        private string $userEmail,
    ) {
    }

    public function getTournamentId(): int
    {
        return $this->tournamentId;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }
}
