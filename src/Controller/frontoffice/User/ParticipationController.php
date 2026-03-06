<?php

namespace App\Controller\frontoffice\User;

use App\Entity\Group;
use App\Entity\Participation;
use App\Entity\User;
use App\Repository\HackathonRepository;
use App\Service\EmailService;
use App\Service\GeminiService;
use App\Service\GoogleCalendarService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/hackathon')]
class ParticipationController extends AbstractController
{
    #[Route('/{id}/tips', name: 'app_hackathon_tips', methods: ['GET'])]
    public function getTips(int $id, HackathonRepository $hackathonRepository, GeminiService $geminiService): JsonResponse
    {
        $hackathon = $hackathonRepository->find($id);
        if (!$hackathon) {
            return new JsonResponse(['error' => 'Hackathon not found'], Response::HTTP_NOT_FOUND);
        }

        $tips = $geminiService->generateHackathonTips($hackathon);

        return new JsonResponse($tips);
    }

    #[Route('/{id}/participate', name: 'app_hackathon_participate', methods: ['POST'])]
    public function participate(
        int $id,
        Request $request,
        HackathonRepository $hackathonRepository,
        EntityManagerInterface $entityManager,
        GoogleCalendarService $calendarService,
        EmailService $emailService
    ): JsonResponse {
        $userId = $request->getSession()->get('user_id');
        $user = $userId ? $entityManager->getRepository(User::class)->find($userId) : null;

        if (!$user) {
            return new JsonResponse(['error' => 'You must be logged in to participate'], Response::HTTP_UNAUTHORIZED);
        }

        $hackathon = $hackathonRepository->find($id);
        if (!$hackathon) {
            return new JsonResponse(['error' => 'Hackathon not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if already participating
        $existingParticipation = $entityManager->getRepository(Participation::class)->findOneBy([
            'hackathon' => $hackathon,
            'status' => 'confirmed' // Or some logic to check if user belongs to a group already participating
        ]);
        // For simplicity, let's just create a solo group if needed.

        // Create a Solo Group for the user
        $group = new Group();
        $group->setName('Solo - ' . $user->getUserIdentifier());
        $group->setDescription('Solo participation for ' . $hackathon->getTitle());
        $group->setLeaderId($user);
        $group->setMaxMembers(1);
        $group->setCreationDate(new \DateTime());
        $group->setType('solo');
        $group->setLevel('beginner'); // Default
        $group->setRatingScore(0.0);
        $group->setIcon('fa-user'); // Default

        $entityManager->persist($group);

        $participation = new Participation();
        $participation->setHackathon($hackathon);
        $participation->setGroupId($group);
        $participation->setStatus('confirmed');
        $participation->setPaymentStatus('free');
        $participation->setPaymentRef('N/A');
        $participation->setRegistredAt(new \DateTimeImmutable());

        $entityManager->persist($participation);
        $entityManager->flush();

        // Send confirmation email (proactive)
        try {
            $emailService->sendParticipationConfirmationEmail($user, $hackathon);
            $this->addFlash('success', 'Participation confirmed! A confirmation email has been sent to ' . $user->getEmail());
            $emailStatus = 'sent';
        } catch (\Exception $e) {
            $this->addFlash('error', 'Participation confirmed, but email could not be sent: ' . $e->getMessage());
            $emailStatus = 'failed: ' . $e->getMessage();
        }

        return new JsonResponse([
            'success' => true,
            'email_status' => $emailStatus,
            'calendar_url' => $calendarService->generateUrl($hackathon)
        ]);
    }
}
