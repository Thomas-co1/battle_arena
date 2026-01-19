<?php

namespace App\DataFixtures;

use App\Entity\Player;
use App\Entity\Tournament;
use App\Entity\TournamentStatus;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const SMASH_CHARACTERS = [
        'Mario', 'Link', 'Donkey Kong', 'Samus', 'Pikachu', 'Yoshi',
        'Kirby', 'Fox', 'Falco', 'Ness', 'Jigglypuff', 'Peach',
        'Bowser', 'Wario', 'Zelda', 'Sheik', 'Ice Climbers', 'Marth',
        'Roy', 'Pichu', 'Mewtwo', 'Chrom', 'Lucina', 'Robin',
        'Corrin', 'Bayonetta', 'Hero', 'Banjo-Kazooie',
    ];

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Create admin user
        $admin = new User();
        $admin->setEmail('admin@battlearena.com');
        $admin->setUsername('admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(true);
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin123');
        $admin->setPassword($hashedPassword);
        $manager->persist($admin);

        // Create 10 test players
        $players = [];
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setEmail("player{$i}@battlearena.com");
            $user->setUsername("player{$i}");
            // Mark first player as unverified for testing email verification flow
            $user->setIsVerified($i !== 1);
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
            $user->setPassword($hashedPassword);

            $player = new Player();
            $player->setGamertag("ProPlayer{$i}");
            $player->setSkillLevel(rand(5, 15));
            $player->setMainCharacter(self::SMASH_CHARACTERS[array_rand(self::SMASH_CHARACTERS)]);
            $player->setUser($user);
            $user->setPlayer($player);

            $manager->persist($user);
            $manager->persist($player);
            $players[] = $player;
        }

        // Create tournaments
        $now = new \DateTimeImmutable();

        $upcomingTournament = new Tournament();
        $upcomingTournament->setName('Smash Bros Tournament - Janvier 2026');
        $upcomingTournament->setDescription('Un grand tournoi de Smash Bros Ultimate avec les meilleurs joueurs de la région');
        $upcomingTournament->setStartDate($now->modify('+7 days'));
        $upcomingTournament->setMaxPlayers(16);
        $upcomingTournament->setStatus(TournamentStatus::PENDING);
        $manager->persist($upcomingTournament);

        $activeTournament = new Tournament();
        $activeTournament->setName('Smash Bros Open - En cours');
        $activeTournament->setDescription('Tournoi actuellement en cours');
        $activeTournament->setStartDate($now->modify('-2 days'));
        $activeTournament->setStatus(TournamentStatus::ACTIVE);
        $activeTournament->setMaxPlayers(8);
        $manager->persist($activeTournament);

        $manager->flush();
    }
}
