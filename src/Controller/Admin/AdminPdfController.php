<?php

namespace App\Controller\Admin;

use App\Message\GenerateTournamentPdf;
use App\Repository\TournamentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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
        return $this->redirectToRoute('admin_tournament_show', ['id' => $id]);
    }

    #[Route('/tournament/{id}/download-pdf', name: 'admin_tournament_download_pdf', methods: ['GET'])]
    public function downloadTournamentPdf(int $id, TournamentRepository $tournamentRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $tournament = $tournamentRepository->find($id);
        if (!$tournament) {
            throw $this->createNotFoundException('Tournoi non trouvé');
        }

        $pdfPath = $this->getParameter('kernel.project_dir') . '/public/uploads/tournament_' . $id . '.pdf';
        
        // Si le fichier est un HTML (cas simplifié), convertir en réponse
        $htmlPath = $this->getParameter('kernel.project_dir') . '/public/uploads/tournament_' . $id . '.html';
        
        if (file_exists($htmlPath)) {
            $pdfPath = $htmlPath;
        }

        if (!file_exists($pdfPath)) {
            $this->addFlash('error', 'Le fichier PDF n\'existe pas encore. Veuillez d\'abord générer le PDF.');
            return $this->redirectToRoute('admin_tournament_show', ['id' => $id]);
        }

        $response = new BinaryFileResponse($pdfPath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'tournament_' . $tournament->getName() . '.pdf'
        );

        return $response;
    }
}

        return $this->redirectToRoute('admin_tournament_show', ['id' => $tournament->getId()]);
    }
}
