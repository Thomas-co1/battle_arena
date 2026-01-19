<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'player', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Pseudo requis')]
    #[Assert\Length(min: 2, max: 50)]
    private ?string $gamertag = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(
        value: 1,
        message: 'Le niveau doit être au minimum 1'
    )]
    #[Assert\LessThanOrEqual(
        value: 15,
        message: 'Le niveau ne peut pas dépasser 15'
    )]
    private ?int $skillLevel = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Veuillez sélectionner un personnage')]
    private ?string $mainCharacter = null;

    #[ORM\Column]
    private int $wins = 0;

    #[ORM\Column]
    private int $losses = 0;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(targetEntity: MatchResult::class, mappedBy: 'player1', cascade: ['remove'])]
    private Collection $matchesAsPlayer1;

    #[ORM\OneToMany(targetEntity: MatchResult::class, mappedBy: 'player2', cascade: ['remove'])]
    private Collection $matchesAsPlayer2;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->matchesAsPlayer1 = new ArrayCollection();
        $this->matchesAsPlayer2 = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getGamertag(): ?string
    {
        return $this->gamertag;
    }

    public function setGamertag(string $gamertag): static
    {
        $this->gamertag = $gamertag;
        return $this;
    }

    public function getSkillLevel(): ?int
    {
        return $this->skillLevel;
    }

    public function setSkillLevel(int $skillLevel): static
    {
        $this->skillLevel = $skillLevel;
        return $this;
    }

    public function getMainCharacter(): ?string
    {
        return $this->mainCharacter;
    }

    public function setMainCharacter(string $mainCharacter): static
    {
        $this->mainCharacter = $mainCharacter;
        return $this;
    }

    public function getWins(): int
    {
        return $this->wins;
    }

    public function setWins(int $wins): static
    {
        $this->wins = $wins;
        return $this;
    }

    public function addWin(): static
    {
        $this->wins++;
        return $this;
    }

    public function getLosses(): int
    {
        return $this->losses;
    }

    public function setLosses(int $losses): static
    {
        $this->losses = $losses;
        return $this;
    }

    public function addLoss(): static
    {
        $this->losses++;
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

    /**
     * @return Collection<int, MatchResult>
     */
    public function getMatchesAsPlayer1(): Collection
    {
        return $this->matchesAsPlayer1;
    }

    /**
     * @return Collection<int, MatchResult>
     */
    public function getMatchesAsPlayer2(): Collection
    {
        return $this->matchesAsPlayer2;
    }

    /**
     * Get all matches for this player (both as player1 and player2)
     * @return Collection<int, MatchResult>
     */
    public function getAllMatches(): Collection
    {
        $allMatches = new ArrayCollection();
        foreach ($this->matchesAsPlayer1 as $match) {
            $allMatches->add($match);
        }
        foreach ($this->matchesAsPlayer2 as $match) {
            $allMatches->add($match);
        }
        return $allMatches;
    }

    public function getWinRate(): float
    {
        $total = $this->wins + $this->losses;
        if ($total === 0) {
            return 0;
        }
        return round(($this->wins / $total) * 100, 2);
    }
}
