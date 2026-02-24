<?php

namespace App\Service;

use App\Entity\Hackathon;
use App\Entity\Participation;
use App\Entity\SponsorHackathon;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailService
{
    private MailerInterface $mailer;
    private Environment $twig;
    private string $mailerFrom;
    private string $contractSignedEmail;

    public function __construct(
        MailerInterface $mailer,
        Environment $twig,
        string $mailerFrom,
        string $contractSignedEmail
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->mailerFrom = $mailerFrom;
        $this->contractSignedEmail = $contractSignedEmail;
    }

    /**
     * Sends an email to the administrator when a sponsor signed a contract.
     */
    public function sendContractSignedEmail(SponsorHackathon $sponsorHackathon, \DateTimeImmutable $signedAt): void
    {
        $email = (new Email())
            ->from($this->mailerFrom)
            ->to($this->contractSignedEmail)
            ->subject('Contract signed – ' . $sponsorHackathon->getSponsor()->getName() . ' / ' . $sponsorHackathon->getHackathon()->getTitle())
            ->html($this->twig->render('backoffice/sponsor_hackathon/email_contract_signed.html.twig', [
                'sponsor_hackathon' => $sponsorHackathon,
                'signed_at' => $signedAt,
            ]));

        $this->mailer->send($email);
    }

    /**
     * Sends a confirmation email to the student when they participate in a hackathon.
     */
    public function sendParticipationConfirmationEmail(User $user, Hackathon $hackathon): void
    {
        $email = (new Email())
            ->from($this->mailerFrom)
            ->to($user->getEmail())
            ->subject('Participation Confirmed: ' . $hackathon->getTitle())
            ->html($this->twig->render('frontoffice/emails/participation_confirmed.html.twig', [
                'user' => $user,
                'hackathon' => $hackathon,
            ]));

        $this->mailer->send($email);
    }
}
