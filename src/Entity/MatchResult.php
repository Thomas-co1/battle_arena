<?php

namespace App\Entity;

use App\Repository\MatchResultRepository;
use App\Validator\Constraints\UniqueMatchOpponents;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MatchResultRepository::class)]
#[ORM\UniqueConstraint(
    name: 'unique_match_opponents',
    columns: ['tournament_id', 'player1_id', 'player2_id']
)]
#[UniqueMatchOpponents]
class MatchResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'matchesAsPlayer1')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?Player $player1 = null;

    #[ORM\ManyToOne(inversedBy: 'matchesAsPlayer2')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?Player $player2 = null;

    #[ORM\ManyToOne(inversedBy: 'matches')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?Tournament $tournament = null;

    #[ORM\Column(enumType: MatchStatus::class)]
    private MatchStatus $status = MatchStatus::PENDING;

    #[ORM\Column(nullable: true)]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Le score doit être positif')]
    private ?int $player1Score = null;

    #[ORM\Column(nullable: true)]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Le score doit être positif')]
    private ?int $player2Score = null;

    #[ORM\Column(enumType: MatchResultType::class, nullable: true)]
    private ?MatchResultType $player1Result = null;

    #[ORM\Column(enumType: MatchResultType::class, nullable: true)]
    private ?MatchResultType $player2Result = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $scheduledDate = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $playedDate = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $moderationNotes = null;

    #[ORM\Column]
    private bool $needsModeration = false;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayer1(): ?Player
    {
        return $this->player1;
    }

    public function setPlayer1(?Player $player1): static
    {
        $this->player1 = $player1;
        return $this;
    }

    public function getPlayer2(): ?Player
    {
        return $this->player2;
    }

    public function setPlayer2(?Player $player2): static
    {
        $this->player2 = $player2;
        return $this;
    }

    public function getTournament(): ?Tournament
    {
        return $this->tournament;
    }

    public function setTournament(?Tournament $tournament): static
    {
        $this->tournament = $tournament;
        return $this;
    }

    public function getStatus(): MatchStatus
    {
        return $this->status;
    }

    public function setStatus(MatchStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getPlayer1Score(): ?int
    {
        return $this->player1Score;
    }

    public function setPlayer1Score(?int $player1Score): static
    {
        $this->player1Score = $player1Score;
        return $this;
    }

    public function getPlayer2Score(): ?int
    {
        return $this->player2Score;
    }

    public function setPlayer2Score(?int $player2Score): static
    {
        $this->player2Score = $player2Score;
        return $this;
    }

    public function getPlayer1Result(): ?MatchResultType
    {
        return $this->player1Result;
    }

    public function setPlayer1Result(?MatchResultType $player1Result): static
    {
        $this->player1Result = $player1Result;
        return $this;
    }

    public function getPlayer2Result(): ?MatchResultType
    {
        return $this->player2Result;
    }

    public function setPlayer2Result(?MatchResultType $player2Result): static
    {
        $this->player2Result = $player2Result;
        return $this;
    }

    public function getScheduledDate(): ?\DateTimeImmutable
    {
        return $this->scheduledDate;
    }

    public function setScheduledDate(?\DateTimeImmutable $scheduledDate): static
    {
        $this->scheduledDate = $scheduledDate;
        return $this;
    }

    public function getPlayedDate(): ?\DateTimeImmutable
    {
        return $this->playedDate;
    }

    public function setPlayedDate(?\DateTimeImmutable $playedDate): static
    {
        $this->playedDate = $playedDate;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getModerationNotes(): ?string
    {
        return $this->moderationNotes;
    }

    public function setModerationNotes(?string $moderationNotes): static
    {
        $this->moderationNotes = $moderationNotes;
        return $this;
    }

    public function isNeedsModeration(): bool
    {
        return $this->needsModeration;
    }

    public function setNeedsModeration(bool $needsModeration): static
    {
        $this->needsModeration = $needsModeration;
        return $this;
    }

    public function isPending(): bool
    {
        return $this->status === MatchStatus::PENDING;
    }

    public function isFinished(): bool
    {
        return $this->status === MatchStatus::FINISHED;
    }

    public function hasResultsSubmitted(): bool
    {
        return $this->player1Result !== null && $this->player2Result !== null;
    }

    public function resultsAreConsistent(): bool
    {
        if (!$this->hasResultsSubmitted()) {
            return false;
        }
        return $this->player1Result === $this->player2Result;
    }

    /**
     * Determine winner based on submitted results
     */
    public function getWinner(): ?Player
    {
        if (!$this->hasResultsSubmitted() || !$this->resultsAreConsistent()) {
            return null;
        }

        return match ($this->player1Result) {
            MatchResultType::WIN => $this->player1,
            MatchResultType::LOSS => $this->player2,
            MatchResultType::DRAW => null,
        };
    }

    /**
     * Check if both players have submitted results (even if inconsistent)
     */
    public function bothPlayersSubmitted(): bool
    {
        return $this->player1Result !== null && $this->player2Result !== null;
    }
}
