<?php

namespace App\Entity;

enum TournamentStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case FINISHED = 'finished';
    case CANCELLED = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            TournamentStatus::PENDING => 'En attente',
            TournamentStatus::ACTIVE => 'En cours',
            TournamentStatus::FINISHED => 'Terminé',
            TournamentStatus::CANCELLED => 'Annulé',
        };
    }
}
