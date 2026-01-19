<?php

namespace App\Service;

use App\Entity\User;
use SymfonyCasts\Bundle\VerifyEmail\Exception\ExpiredSignatureException;
use SymfonyCasts\Bundle\VerifyEmail\Exception\InvalidSignatureException;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailVerificationService
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function sendVerificationEmail(User $user): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            'app_verify_email',
            $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()]
        );

        $email = (new Email())
            ->from(new Address('noreply@battlearena.com', 'Battle Arena'))
            ->to($user->getEmail())
            ->subject('Confirme ton email - Battle Arena')
            ->html($this->generateEmailContent($signatureComponents->getSignedUrl(), $user));

        $this->mailer->send($email);
    }

    public function verifySignature(string $signature, string $email): void
    {
        try {
            $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
                ['signature' => $signature, 'expires' => $_GET['expires'] ?? null, 'token' => $_GET['token'] ?? null],
                $email
            );
        } catch (ExpiredSignatureException $e) {
            throw new \Exception('Le lien de confirmation a expiré');
        } catch (InvalidSignatureException $e) {
            throw new \Exception('Lien de confirmation invalide');
        }
    }

    private function generateEmailContent(string $verifyUrl, User $user): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
        .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚔️ Battle Arena</h1>
        </div>
        <div class="content">
            <h2>Bienvenue, {{ $user->getUsername() }}!</h2>
            <p>Pour activer ton compte Battle Arena, tu dois confirmer ton adresse email.</p>
            
            <p style="text-align: center;">
                <a href="{$verifyUrl}" class="button">Confirmer mon email</a>
            </p>
            
            <p>Si tu n'as pas créé ce compte, ignore ce message.</p>
            
            <p style="color: #999; font-size: 12px;">
                Ce lien expire dans 24 heures.
            </p>
        </div>
        <div class="footer">
            <p>&copy; 2026 Battle Arena. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
