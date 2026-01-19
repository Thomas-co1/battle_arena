<?php

namespace App\MessageHandler;

use App\Message\GenerateTournamentPdf;
use App\Repository\TournamentRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Twig\Environment;

#[AsMessageHandler]
class GenerateTournamentPdfHandler
{
    public function __construct(
        private TournamentRepository $tournamentRepository,
        private Environment $twig,
        private MailerInterface $mailer,
    ) {
    }

    public function __invoke(GenerateTournamentPdf $message): void
    {
        $tournament = $this->tournamentRepository->find($message->getTournamentId());

        if (!$tournament) {
            return;
        }

        try {
            // Générer le HTML du PDF
            $html = $this->twig->render('pdf/tournament.html.twig', [
                'tournament' => $tournament,
            ]);

            // Créer le répertoire uploads s'il n'existe pas
            $uploadsDir = sprintf('%s/public/uploads', getcwd());
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }

            // Sauvegarder le HTML comme PDF (simple version)
            $fileName = sprintf('tournament_%d_%s.html', $tournament->getId(), date('Y-m-d-His'));
            $filePath = sprintf('%s/%s', $uploadsDir, $fileName);
            file_put_contents($filePath, $html);

            // Envoyer un email avec le lien du PDF
            $email = (new Email())
                ->from('noreply@battlearena.com')
                ->to($message->getUserEmail())
                ->subject('Récapitulatif du tournoi: ' . $tournament->getName())
                ->html($html);

            $this->mailer->send($email);
        } catch (\Exception $e) {
            error_log('Error generating tournament PDF: ' . $e->getMessage());
        }
    }
}

