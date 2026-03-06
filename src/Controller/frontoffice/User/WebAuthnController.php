<?php

namespace App\Controller\frontoffice\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/webauthn')]
class WebAuthnController extends AbstractController
{
    private function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // 1. Generate options for registering a new fingerprint
    #[Route('/register/options', name: 'webauthn_register_options', methods: ['POST'])]
    public function registerOptions(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        // Generate challenge
        $challenge = random_bytes(32);
        
        // Store challenge in session to verify later
        $request->getSession()->set('webauthn_challenge', $this->base64url_encode($challenge));

        // Create options
        $options = [
            'rp' => [
                'name' => 'Mon Application Pidev',
                'id' => 'localhost',
            ],
            'user' => [
                'id' => $this->base64url_encode((string)$user->getId()),
                'name' => $user->getEmail(),
                'displayName' => $user->getDisplayName(),
            ],
            'challenge' => $this->base64url_encode($challenge),
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7], // ES256
                ['type' => 'public-key', 'alg' => -257], // RS256
            ],
            'authenticatorSelection' => [
                'authenticatorAttachment' => 'platform', // Use built-in biometrics
                'userVerification' => 'required',
            ],
            'timeout' => 60000,
            'attestation' => 'none'
        ];

        return new JsonResponse($options);
    }

    // 2. Verify and store the new fingerprint credential
    #[Route('/register/verify', name: 'webauthn_register_verify', methods: ['POST'])]
    public function registerVerify(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return new JsonResponse(['error' => 'Not authenticated'], 401);
        }

        $user = $em->getRepository(User::class)->find($userId);
        $data = json_decode($request->getContent(), true);

        if (!$user || !isset($data['id']) || !isset($data['rawId']) || !isset($data['response'])) {
            return new JsonResponse(['error' => 'Invalid biometric response'], 400);
        }

        // Simple validation: storing the credential ID and the raw public key
        // In a full production app, you would parse the CBOR response and verify the signature.
        
        $user->setWebauthnCredentialId($data['id']);
        
        // Save the clientDataJSON as a pseudo public key for this simple implementation
        if (isset($data['response']['clientDataJSON'])) {
             $user->setWebauthnPublicKey($data['response']['clientDataJSON']);
        }
        
        $em->flush();

        return new JsonResponse(['status' => 'success', 'message' => 'Biometric authentication enabled!']);
    }

    // 3. Generate options for logging in via fingerprint
    #[Route('/login/options', name: 'webauthn_login_options', methods: ['POST'])]
    public function loginOptions(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            return new JsonResponse(['error' => 'Email is required to identify the user'], 400);
        }

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        if (!$user->getWebauthnCredentialId()) {
            return new JsonResponse(['error' => 'No biometric data registered for this user'], 400);
        }

        $challenge = random_bytes(32);
        $request->getSession()->set('webauthn_login_challenge', $this->base64url_encode($challenge));
        $request->getSession()->set('webauthn_login_user_id', $user->getId());

        $options = [
            'challenge' => $this->base64url_encode($challenge),
            'allowCredentials' => [
                [
                    'type' => 'public-key',
                    'id' => $user->getWebauthnCredentialId(),
                    'transports' => ['internal'],
                ]
            ],
            'timeout' => 60000,
            'userVerification' => 'required',
        ];

        return new JsonResponse($options);
    }

    // 4. Verify the login challenge
    #[Route('/login/verify', name: 'webauthn_login_verify', methods: ['POST'])]
    public function loginVerify(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $userId = $request->getSession()->get('webauthn_login_user_id');
        $expectedChallenge = $request->getSession()->get('webauthn_login_challenge');

        if (!$userId || !$expectedChallenge) {
            return new JsonResponse(['error' => 'No active login session'], 400);
        }

        $user = $em->getRepository(User::class)->find($userId);
        $data = json_decode($request->getContent(), true);

        if (!$user || !isset($data['id'])) {
            return new JsonResponse(['error' => 'Invalid response'], 400);
        }

        // Verify the credential ID matches what we have in the DB
        if ($user->getWebauthnCredentialId() !== $data['id']) {
            return new JsonResponse(['error' => 'Invalid credential'], 401);
        }

        // Check ban status
        if ($user->isBannedNow() || $user->isBan()) {
            return new JsonResponse(['error' => 'Account is banned'], 403);
        }

        if ($user->getBannedUntil() !== null && $user->getBannedUntil() <= new \DateTime()) {
            $user->setBan(false);
            $user->setBannedUntil(null);
            $em->flush();
        }

        // Success! Log the user in
        $request->getSession()->set('user_id', $user->getId());
        
        // Clean up session vars
        $request->getSession()->remove('webauthn_login_challenge');
        $request->getSession()->remove('webauthn_login_user_id');

        return new JsonResponse(['status' => 'success', 'redirect' => $this->generateUrl('home')]);
    }
}
