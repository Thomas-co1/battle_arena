<?php

namespace App\Controller\Admin;

use App\Message\GenerateTournamentPdf;
use App\Repository\TournamentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminPdfController extends AbstractController
{
    #[Route('/tournament/{id}/generate-pdf', name: 'admin_tournament_generate_pdf', methods: ['POST'])]
    public function generateTournamentPdf(int $id, TournamentRepository $tournamentRepository, MessageBusInterface $bus): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $tournament = $tournamentRepository->find($id);
        if (!$tournament) {
            throw $this->createNotFoundException('Tournoi non trouvé');
        }

        // Envoyer le message au bus pour traitement asynchrone
        $message = new GenerateTournamentPdf($tournament->getId(), $this->getUser()->getEmail());
        $bus->dispatch($message);

        $this->addFlash('success', 'La génération du PDF a été lancée. Vous recevrez un email avec le fichier.');

        return $this->redirectToRoute('admin_tournament_show', ['id' => $tournament->getId()]);
    }
}
