<?php

namespace App\MessageHandler;

use App\Message\GenerateTournamentPdf;
use App\Repository\TournamentRepository;
use Knp\Snappy\Pdf;
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
        private Pdf $pdf,
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

            // Générer le PDF
            $pdf = $this->pdf->getOutputFromHtml($html);

            // Sauvegarder le PDF
            $fileName = sprintf('tournament_%d_%s.pdf', $tournament->getId(), date('Y-m-d'));
            $filePath = sprintf('%s/var/uploads/%s', getcwd(), $fileName);
            file_put_contents($filePath, $pdf);

            // Envoyer un email avec le PDF
            $email = (new Email())
                ->from('noreply@battlearena.com')
                ->to($message->getUserEmail())
                ->subject('Récapitulatif du tournoi: ' . $tournament->getName())
                ->htmlTemplate('email/tournament_pdf.html.twig')
                ->context(['tournament' => $tournament])
                ->attachFromPath($filePath);

            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log the error (you might want to implement proper logging)
            error_log('Error generating tournament PDF: ' . $e->getMessage());
        }
    }
}
