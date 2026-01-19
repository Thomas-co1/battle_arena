<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EmailVerificationController extends AbstractController
{
    #[Route('/verify/email/{id<\d+>}', name: 'app_verify_email', methods: ['GET'])]
    public function verifyUserEmail(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        VerifyEmailHelperInterface $verifyEmailHelper
    ): Response {
        $user = $userRepository->find($request->attributes->get('id'));

        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé');
            return $this->redirectToRoute('app_tournament_list');
        }

        try {
            $verifyEmailHelper->validateEmailConfirmationFromRequest($request, $user->getId(), $user->getEmail());
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('error', 'Lien de vérification invalide ou expiré: ' . $e->getReason());
            return $this->redirectToRoute('app_tournament_list');
        }

        // Marquer l'utilisateur comme vérifié
        $user->setIsVerified(true);
        $em->flush();

        $this->addFlash('success', 'Email vérifié avec succès! Vous pouvez maintenant vous connecter.');
        return $this->redirectToRoute('app_login');
    }
}
