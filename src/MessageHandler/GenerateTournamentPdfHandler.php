<?php

namespace App\MessageHandler;

use App\Message\GenerateTournamentPdf;
use App\Repository\TournamentRepository;
use App\Service\PdfService;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
class GenerateTournamentPdfHandler
{
    public function __construct(
        private TournamentRepository $tournamentRepository,
        private PdfService $pdfService,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(GenerateTournamentPdf $message): void
    {
        $tournament = $this->tournamentRepository->find($message->getTournamentId());

        if (!$tournament) {
            return;
        }

        try {
            // Générer le PDF
            $pdfPath = $this->pdfService->generateTournamentPdf($tournament);
            
            // Obtenir le nom du fichier
            $fileName = basename($pdfPath);
            
            // Créer le lien de téléchargement
            $downloadUrl = $this->urlGenerator->generate(
                'admin_tournament_download_pdf',
                ['id' => $tournament->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            // Envoyer un email avec le lien du PDF
            $emailHtml = sprintf('
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                    <div style="background: linear-gradient(135deg, #667eea 0%%, #764ba2 100%%); color: white; padding: 20px; text-align: center;">
                        <h1>⚔️ Battle Arena</h1>
                    </div>
                    <div style="padding: 20px; background: #f9f9f9;">
                        <h2>Récapitulatif du tournoi prêt!</h2>
                        <p>Le PDF du tournoi <strong>%s</strong> a été généré avec succès.</p>
                        <p style="text-align: center; margin: 30px 0;">
                            <a href="%s" style="display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px;">
                                📄 Télécharger le PDF
                            </a>
                        </p>
                        <p style="color: #666; font-size: 12px;">
                            Fichier généré: %s<br>
                            Date: %s
                        </p>
                    </div>
                    <div style="padding: 15px; text-align: center; color: #999; font-size: 12px;">
                        &copy; 2024 Battle Arena. Tous droits réservés.
                    </div>
                </div>
            ', 
                htmlspecialchars($tournament->getName()),
                htmlspecialchars($downloadUrl),
                htmlspecialchars($fileName),
                date('d/m/Y à H:i')
            );

            $email = (new Email())
                ->from('noreply@battlearena.com')
                ->to($message->getUserEmail())
                ->subject('Récapitulatif PDF du tournoi: ' . $tournament->getName())
                ->html($emailHtml);

            $this->mailer->send($email);
        } catch (\Exception $e) {
            error_log('Error generating tournament PDF: ' . $e->getMessage());
            throw $e;
        }
    }
}


