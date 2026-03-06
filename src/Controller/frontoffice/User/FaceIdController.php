<?php

namespace App\Controller\frontoffice\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/faceid')]
class FaceIdController extends AbstractController
{
    /**
     * Register face descriptor for the authenticated user
     */
    #[Route('/register', name: 'faceid_register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['descriptor']) || !is_array($data['descriptor']) || count($data['descriptor']) !== 128) {
            return new JsonResponse(['error' => 'Invalid face descriptor. Expected 128-dimensional array.'], 400);
        }

        // Store as JSON string
        $user->setFaceDescriptor(json_encode($data['descriptor']));
        $em->flush();

        return new JsonResponse(['status' => 'success', 'message' => 'Face ID registered successfully!']);
    }

    /**
     * Login via face recognition
     */
    #[Route('/login', name: 'faceid_login', methods: ['POST'])]
    public function login(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $descriptor = $data['descriptor'] ?? null;

        if (!$email) {
            return new JsonResponse(['error' => 'Email is required'], 400);
        }

        if (!$descriptor || !is_array($descriptor) || count($descriptor) !== 128) {
            return new JsonResponse(['error' => 'Invalid face descriptor'], 400);
        }

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $storedDescriptor = $user->getFaceDescriptor();
        if (!$storedDescriptor) {
            return new JsonResponse(['error' => 'No Face ID registered for this user. Please set up Face ID in your profile first.'], 400);
        }

        $storedArray = json_decode($storedDescriptor, true);
        if (!$storedArray || count($storedArray) !== 128) {
            return new JsonResponse(['error' => 'Stored face data is corrupted'], 500);
        }

        // Calculate Euclidean distance between the two face descriptors
        $distance = $this->euclideanDistance($descriptor, $storedArray);

        // Threshold: typically 0.6 is a good match threshold for face-api.js
        $threshold = 0.6;

        if ($distance > $threshold) {
            return new JsonResponse([
                'error' => 'Face not recognized. Please try again or use password login.',
                'distance' => round($distance, 4)
            ], 401);
        }

        // Check ban status
        if ($user->isBannedNow() || $user->isBan()) {
            return new JsonResponse(['error' => 'Account is banned'], 403);
        }

        // Auto-unban if ban period expired
        if ($user->getBannedUntil() !== null && $user->getBannedUntil() <= new \DateTime()) {
            $user->setBan(false);
            $user->setBannedUntil(null);
            $em->flush();
        }

        // Log in the user
        $request->getSession()->set('user_id', $user->getId());

        return new JsonResponse([
            'status' => 'success',
            'redirect' => $this->generateUrl('home'),
            'distance' => round($distance, 4)
        ]);
    }

    /**
     * Calculate Euclidean distance between two vectors
     *
     * @param list<float|int> $a
     * @param list<float|int> $b
     */
    private function euclideanDistance(array $a, array $b): float
    {
        $sum = 0;
        for ($i = 0; $i < count($a); $i++) {
            $diff = $a[$i] - $b[$i];
            $sum += $diff * $diff;
        }
        return sqrt($sum);
    }
}
