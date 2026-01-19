<?php

namespace App\Service;

use App\Entity\Player;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository,
    ) {
    }

    public function register(string $email, string $username, string $plainPassword, string $gamertag, int $skillLevel, string $mainCharacter): User
    {
        // Vérifier l'email unique
        if ($this->userRepository->findByEmail($email)) {
            throw new \Exception('Un compte avec cet email existe déjà');
        }

        // Créer l'utilisateur
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        $user->setIsVerified(false); // À vérifier par email

        // Créer le profil joueur
        $player = new Player();
        $player->setGamertag($gamertag);
        $player->setSkillLevel($skillLevel);
        $player->setMainCharacter($mainCharacter);
        $player->setUser($user);

        $user->setPlayer($player);

        $this->entityManager->persist($user);
        $this->entityManager->persist($player);
        $this->entityManager->flush();

        return $user;
    }

    public function verifyEmail(User $user): void
    {
        $user->setIsVerified(true);
        $this->entityManager->flush();
    }
}
