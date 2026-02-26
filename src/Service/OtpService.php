<?php

namespace App\Service;

use App\Entity\EmailVerification;
use App\Repository\EmailVerificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Psr\Log\LoggerInterface;

class OtpService
{
    private bool $emailSent = false;

    public function __construct(
        private EntityManagerInterface $em,
        private EmailVerificationRepository $verificationRepo,
        private MailerInterface $mailer,
        private Environment $twig,
        private ?LoggerInterface $logger = null,
    ) {}

    /**
     * Generate a 6-digit OTP, store it in DB, and try to send it via email.
     * Returns the code even if email sending fails.
     */
    public function sendOtp(string $toEmail): string
    {
        // Clean up any previous codes for this email
        $this->verificationRepo->removeAllForEmail($toEmail);

        // Generate a random 6-digit code
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store in database
        $verification = new EmailVerification();
        $verification->setEmail($toEmail);
        $verification->setCode($code);
        $verification->setExpiresAt(new \DateTime('+10 minutes'));

        $this->em->persist($verification);
        $this->em->flush();

        // Try to send email
        try {
            $htmlContent = $this->twig->render('emails/otp_verification.html.twig', [
                'code' => $code,
                'email' => $toEmail,
            ]);

            $email = (new Email())
                ->from('tas.sam.se@gmail.com') // Authenticated Gmail sender
                ->to($toEmail)
                ->subject('Your verification code - ' . $code)
                ->html($htmlContent);

            $this->mailer->send($email);
            $this->emailSent = true;
        } catch (\Throwable $e) {
            $this->emailSent = false;
            $this->logger?->error('OTP email failed: ' . $e->getMessage());
        }

        return $code;
    }

    /**
     * Whether the last sendOtp() call successfully sent the email.
     */
    public function wasEmailSent(): bool
    {
        return $this->emailSent;
    }

    /**
     * Verify an OTP code for a given email.
     * Returns true if the code is valid and not expired.
     */
    public function verifyOtp(string $email, string $code): bool
    {
        $verification = $this->verificationRepo->findValidCode($email, $code);

        if (!$verification) {
            return false;
        }

        // Code is valid — clean up all codes for this email
        $this->verificationRepo->removeAllForEmail($email);

        return true;
    }
}
