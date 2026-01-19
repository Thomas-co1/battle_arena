<?php

namespace App\Entity;

enum MatchStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case FINISHED = 'finished';
    case CANCELLED = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            MatchStatus::PENDING => 'En attente',
            MatchStatus::IN_PROGRESS => 'En cours',
            MatchStatus::FINISHED => 'Terminé',
            MatchStatus::CANCELLED => 'Annulé',
        };
    }
}
