<?php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use App\Entity\Tournament;

class PdfService
{
    private string $pdfDirectory;

    public function __construct(private Environment $twig, string $kernelProjectDir)
    {
        $this->pdfDirectory = $kernelProjectDir . '/public/uploads';
        
        // Créer le dossier s'il n'existe pas
        if (!is_dir($this->pdfDirectory)) {
            mkdir($this->pdfDirectory, 0777, true);
        }
    }
    
    public function generateTournamentPdf(Tournament $tournament): string
    {
        $filename = 'tournament_' . $tournament->getId() . '_' . time() . '.pdf';
        $path = $this->pdfDirectory . '/' . $filename;
        
        $option = new Options();
        $option->set('defaultFont', 'Arial');

        try {
            $dompdf = new Dompdf($option);
            $html = $this->twig->render('pdf/tournament_recap.html.twig', [
                'tournament' => $tournament,
            ]);
            
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            file_put_contents($path, $dompdf->output());
            
            return $path;
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la génération du PDF : ' . $e->getMessage());
        }
    }
}
