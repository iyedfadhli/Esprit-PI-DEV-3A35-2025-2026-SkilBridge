<?php

namespace App\Controller\frontoffice\User;

use App\Entity\User;
use App\Entity\Student;
use App\Entity\Supervisor;
use App\Entity\Entreprise;
use App\Form\LoginType;
use App\Service\OtpService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class UserController extends AbstractController
{
    #[Route('/sign_up_in', name: 'sign')]
    public function signUpIn(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        OtpService $otpService
    ): Response {

        $loginForm = $this->createForm(LoginType::class);
        $loginForm->handleRequest($request);

        // ================= REGISTER =================
        if ($request->isMethod('POST') && $request->request->has('register_submit')) {

            $type  = $request->request->get('type');
            $nom   = trim((string) $request->request->get('nom'));
            $prenom = trim((string) $request->request->get('prenom'));
            $email = strtolower(trim((string) $request->request->get('email')));
            $dateNaissance = $request->request->get('date_naissance');
            $domaine = $request->request->get('domaine');
            $passwd = (string) $request->request->get('passwd');

            // ✅ PHP VALIDATION (mapped to fields)
            $fieldErrors = [];

            // Validate type
            if (empty($type)) {
                $fieldErrors['type'] = 'Account type is required.';
            } elseif (!in_array($type, ['student', 'supervisor', 'entreprise'])) {
                $fieldErrors['type'] = 'Invalid account type.';
            }

            // Validate nom
            if (empty($nom)) {
                $fieldErrors['nom'] = 'Name is required.';
            } elseif (strlen($nom) < 2) {
                $fieldErrors['nom'] = 'Name must be at least 2 characters.';
            } elseif (strlen($nom) > 30) {
                $fieldErrors['nom'] = 'Name must not exceed 30 characters.';
            }

            // Validate email
            if (empty($email)) {
                $fieldErrors['email'] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $fieldErrors['email'] = 'Please enter a valid email address.';
            }

            // Validate password
            if (empty($passwd)) {
                $fieldErrors['passwd'] = 'Password is required.';
            } elseif (strlen($passwd) < 6) {
                $fieldErrors['passwd'] = 'Password must be at least 6 characters.';
            }

            // Validate prenom for student/supervisor
            if (in_array($type, ['student', 'supervisor'])) {
                if (!empty($prenom) && strlen($prenom) < 2) {
                    $fieldErrors['prenom'] = 'First name must be at least 2 characters.';
                }
            }

            // Validate domaine for entreprise
            if ($type === 'entreprise' && !empty($domaine) && strlen($domaine) > 255) {
                $fieldErrors['domaine'] = 'Domain must not exceed 255 characters.';
            }

            // Check if email already exists
            if (empty($fieldErrors['email']) && $em->getRepository(User::class)->findOneBy(['email' => $email])) {
                $fieldErrors['email'] = 'Email already exists!';
            }

            // If validation errors exist, render template with errors and old data
            if (!empty($fieldErrors)) {
                return $this->render('frontoffice/user/sign.html.twig', [
                    'form' => $loginForm->createView(),
                    'registerErrors' => $fieldErrors,
                    'registerData' => $request->request->all(),
                    'showRegister' => true // Flag to keep register tab active
                ]);
            }

            // ✅ Store registration data in session and send OTP
            $session = $request->getSession();
            $session->set('pending_registration', [
                'type' => $type,
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'date_naissance' => $dateNaissance,
                'domaine' => $domaine,
                'passwd' => $passwd,
            ]);

            $code = $otpService->sendOtp($email);

            if ($otpService->wasEmailSent()) {
                $this->addFlash('success', 'A verification code has been sent to your email.');
            } else {
                // Email failed — store code in session so we can show it on the page
                $session->set('otp_fallback_code', $code);
                $this->addFlash('warning', 'Email could not be sent. Your code is displayed below.');
            }
            return $this->redirectToRoute('verify_otp');
        }

        // ================= LOGIN =================
        if ($loginForm->isSubmitted() && $loginForm->isValid()) {

            $formData = $loginForm->getData();
            $email = strtolower(trim((string) $formData['email']));
            $password = (string) $formData['password'];

            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
                $this->addFlash('error', 'Invalid email or password');
            } else {
                // Check timed ban (bannedUntil)
                if ($user->isBannedNow()) {
                    $this->addFlash('error', 'Your account is banned until ' . $user->getBannedUntil()->format('d/m/Y H:i'));
                    return $this->redirectToRoute('sign');
                }

                // Auto-unban if ban has expired
                if ($user->getBannedUntil() !== null && $user->getBannedUntil() <= new \DateTime()) {
                    $user->setBan(false);
                    $user->setBannedUntil(null);
                    $em->flush();
                }

                if ($user->isBan()) {
                    $this->addFlash('error', 'Your account is banned');
                    return $this->redirectToRoute('sign');
                }

                $request->getSession()->set('user_id', $user->getId());
                $this->addFlash('success', 'Logged in successfully!');
                return $this->redirectToRoute('home');
            }
        }

        return $this->render('frontoffice/user/sign.html.twig', [
            'form' => $loginForm->createView(),
        ]);
    }

    // ================= VERIFY OTP =================
    #[Route('/verify-otp', name: 'verify_otp', methods: ['GET', 'POST'])]
    public function verifyOtp(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        OtpService $otpService
    ): Response {
        $session = $request->getSession();
        $pendingData = $session->get('pending_registration');

        if (!$pendingData) {
            $this->addFlash('error', 'No pending registration. Please fill the form first.');
            return $this->redirectToRoute('sign');
        }

        $email = $pendingData['email'];

        if ($request->isMethod('POST')) {
            $code = trim((string) $request->request->get('otp_code'));

            if (strlen($code) !== 6) {
                $this->addFlash('error', 'Please enter the complete 6-digit code.');
                return $this->render('frontoffice/user/verify_otp.html.twig', ['email' => $email, 'fallback_code' => $session->get('otp_fallback_code')]);
            }

            if (!$otpService->verifyOtp($email, $code)) {
                $this->addFlash('error', 'Invalid or expired code. Please try again or resend.');
                return $this->render('frontoffice/user/verify_otp.html.twig', ['email' => $email, 'fallback_code' => $session->get('otp_fallback_code')]);
            }

            // ✅ OTP verified — create the account
            $type = $pendingData['type'];
            $nom = $pendingData['nom'];
            $prenom = $pendingData['prenom'];
            $dateNaissance = $pendingData['date_naissance'];
            $domaine = $pendingData['domaine'];
            $passwd = $pendingData['passwd'];

            if ($type === 'student') {
                $user = new Student();
                $user->setPrenom($prenom);
                if ($dateNaissance) {
                    $user->setDateNaissance(new \DateTime($dateNaissance));
                }
            } elseif ($type === 'supervisor') {
                $user = new Supervisor();
                $user->setPrenom($prenom);
                if ($dateNaissance) {
                    $user->setDateNaissance(new \DateTime($dateNaissance));
                }
            } elseif ($type === 'entreprise') {
                $user = new Entreprise();
                $user->setDomaine($domaine);
            } else {
                $this->addFlash('error', 'Invalid account type.');
                $session->remove('pending_registration');
                return $this->redirectToRoute('sign');
            }

            $user->setNom($nom);
            $user->setEmail($email);
            $user->setBan(false);

            $hashedPassword = $passwordHasher->hashPassword($user, $passwd);
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

            // Clean up session
            $session->remove('pending_registration');
            $session->remove('otp_fallback_code');

            $this->addFlash('success', 'Account created successfully! You can now log in.');
            return $this->redirectToRoute('sign');
        }

        return $this->render('frontoffice/user/verify_otp.html.twig', ['email' => $email, 'fallback_code' => $session->get('otp_fallback_code')]);
    }

    // ================= RESEND OTP =================
    #[Route('/resend-otp', name: 'resend_otp', methods: ['POST'])]
    public function resendOtp(Request $request, OtpService $otpService): RedirectResponse
    {
        $session = $request->getSession();
        $pendingData = $session->get('pending_registration');

        if (!$pendingData) {
            $this->addFlash('error', 'No pending registration.');
            return $this->redirectToRoute('sign');
        }

        $code = $otpService->sendOtp($pendingData['email']);
        if ($otpService->wasEmailSent()) {
            $session->remove('otp_fallback_code');
            $this->addFlash('success', 'A new verification code has been sent!');
        } else {
            $session->set('otp_fallback_code', $code);
            $this->addFlash('warning', 'Email could not be sent. Your code is displayed below.');
        }
        return $this->redirectToRoute('verify_otp');
    }

    // ================= FORGOT PASSWORD =================
    #[Route('/forgot-password', name: 'forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(Request $request, EntityManagerInterface $em, OtpService $otpService): Response
    {
        if ($request->isMethod('POST')) {
            $email = strtolower(trim((string) $request->request->get('email')));

            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Please enter a valid email address.');
                return $this->render('frontoffice/user/forgot_password.html.twig', ['email' => $email]);
            }

            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
            if (!$user) {
                $this->addFlash('error', 'No account found with this email address.');
                return $this->render('frontoffice/user/forgot_password.html.twig', ['email' => $email]);
            }

            $session = $request->getSession();
            $session->set('forgot_password_email', $email);

            $code = $otpService->sendOtp($email);

            if ($otpService->wasEmailSent()) {
                $this->addFlash('success', 'A verification code has been sent to your email.');
            } else {
                $session->set('forgot_fallback_code', $code);
                $this->addFlash('warning', 'Email could not be sent. Your code is displayed below.');
            }

            return $this->redirectToRoute('forgot_password_verify');
        }

        return $this->render('frontoffice/user/forgot_password.html.twig');
    }

    #[Route('/forgot-password/verify', name: 'forgot_password_verify', methods: ['GET', 'POST'])]
    public function forgotPasswordVerify(Request $request, OtpService $otpService): Response
    {
        $session = $request->getSession();
        $email = $session->get('forgot_password_email');

        if (!$email) {
            $this->addFlash('error', 'Please enter your email first.');
            return $this->redirectToRoute('forgot_password');
        }

        if ($request->isMethod('POST')) {
            $code = trim((string) $request->request->get('otp_code'));

            if (strlen($code) !== 6) {
                $this->addFlash('error', 'Please enter the complete 6-digit code.');
                return $this->render('frontoffice/user/forgot_verify.html.twig', [
                    'email' => $email,
                    'fallback_code' => $session->get('forgot_fallback_code'),
                ]);
            }

            if (!$otpService->verifyOtp($email, $code)) {
                $this->addFlash('error', 'Invalid or expired code. Please try again.');
                return $this->render('frontoffice/user/forgot_verify.html.twig', [
                    'email' => $email,
                    'fallback_code' => $session->get('forgot_fallback_code'),
                ]);
            }

            // Code verified — allow password reset
            $session->set('forgot_password_verified', true);
            $session->remove('forgot_fallback_code');

            return $this->redirectToRoute('forgot_password_reset');
        }

        return $this->render('frontoffice/user/forgot_verify.html.twig', [
            'email' => $email,
            'fallback_code' => $session->get('forgot_fallback_code'),
        ]);
    }

    #[Route('/forgot-password/resend', name: 'forgot_password_resend', methods: ['POST'])]
    public function forgotPasswordResend(Request $request, OtpService $otpService): RedirectResponse
    {
        $session = $request->getSession();
        $email = $session->get('forgot_password_email');

        if (!$email) {
            $this->addFlash('error', 'Please enter your email first.');
            return $this->redirectToRoute('forgot_password');
        }

        $code = $otpService->sendOtp($email);
        if ($otpService->wasEmailSent()) {
            $session->remove('forgot_fallback_code');
            $this->addFlash('success', 'A new verification code has been sent!');
        } else {
            $session->set('forgot_fallback_code', $code);
            $this->addFlash('warning', 'Email could not be sent. Your code is displayed below.');
        }

        return $this->redirectToRoute('forgot_password_verify');
    }

    #[Route('/forgot-password/reset', name: 'forgot_password_reset', methods: ['GET', 'POST'])]
    public function forgotPasswordReset(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $session = $request->getSession();
        $email = $session->get('forgot_password_email');
        $verified = $session->get('forgot_password_verified');

        if (!$email || !$verified) {
            $this->addFlash('error', 'Please verify your email first.');
            return $this->redirectToRoute('forgot_password');
        }

        if ($request->isMethod('POST')) {
            $password = (string) $request->request->get('password');
            $confirmPassword = (string) $request->request->get('confirm_password');

            if (strlen($password) < 6) {
                $this->addFlash('error', 'Password must be at least 6 characters long.');
                return $this->render('frontoffice/user/forgot_reset.html.twig');
            }

            if ($password !== $confirmPassword) {
                $this->addFlash('error', 'Passwords do not match.');
                return $this->render('frontoffice/user/forgot_reset.html.twig');
            }

            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
            if (!$user) {
                $this->addFlash('error', 'Account not found.');
                return $this->redirectToRoute('forgot_password');
            }

            $user->setPassword($passwordHasher->hashPassword($user, $password));
            $em->flush();

            // Clean up session
            $session->remove('forgot_password_email');
            $session->remove('forgot_password_verified');

            $this->addFlash('success', 'Password reset successfully! You can now log in with your new password.');
            return $this->redirectToRoute('sign');
        }

        return $this->render('frontoffice/user/forgot_reset.html.twig');
    }

    #[Route('/logout', name: 'logout')]
    public function logout(Request $request): RedirectResponse
    {
        $request->getSession()->clear();
        return $this->redirectToRoute('home');
    }

    #[Route('/profile', name: 'profile')]
    public function profile(Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('sign');
        }

        $user = $em->getRepository(User::class)->find($userId);

        return $this->render('frontoffice/user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    // ================= EDIT PROFILE =================
    #[Route('/profile/edit/{id}', name: 'profile_edit', methods: ['POST'])]
    public function editProfile(
        int $id,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        $sessionUserId = $request->getSession()->get('user_id');

        if (!$sessionUserId || $sessionUserId != $id) {
            $this->addFlash('error', 'Unauthorized action.');
            return $this->redirectToRoute('sign');
        }

        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->redirectToRoute('profile');
        }

        $user->setNom($request->request->get('nom'));
        $user->setEmail($request->request->get('email'));

        if ($request->request->get('prenom')) {
            $user->setPrenom($request->request->get('prenom'));
        }

        if ($request->request->get('education')) {
            $user->setEducation($request->request->get('education'));
        }

        if ($request->request->get('skills')) {
            $user->setSkills($request->request->get('skills'));
        }

        if ($request->request->get('experience')) {
            $user->setExperience($request->request->get('experience'));
        }

        if ($request->request->get('domaine')) {
            $user->setDomaine($request->request->get('domaine'));
        }

        $em->flush();

        $this->addFlash('success', 'Profile updated successfully!');
        return $this->redirectToRoute('profile');
    }

    // ================= CHANGE PHOTO =================
    #[Route('/profile/change-photo/{id}', name: 'profile_change_photo', methods: ['POST'])]
    public function changePhoto(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->redirectToRoute('profile');
        }

        $file = $request->files->get('profile');

        if ($file) {
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/assets/images/frontoffice/user_pic';
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower($user->getNom()));
            $ext = strtolower($file->getClientOriginalExtension() ?? '');
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (!in_array($ext, $allowed, true)) {
                $ext = 'dat';
            }
            $filename = $safeName . '.' . $ext;

            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0777, true);
            }

            $filename = uniqid() . '.' . ($file->guessExtension() ?: 'jpg');
            $file->move($uploadsDir, $filename);

            $user->setPhoto('assets/images/frontoffice/user_pic/' . $filename);
            $em->flush();
        }

        return $this->redirectToRoute('profile');
    }

    // ================= CHANGE PASSWORD =================
    #[Route('/profile/change-password/{id}', name: 'profile_change_password', methods: ['POST'])]
    public function changePassword(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {

        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->redirectToRoute('profile');
        }

        $currentPassword = $request->request->get('current_password');
        $newPassword = $request->request->get('new_password');
        $confirmPassword = $request->request->get('confirm_password');

        // ✅ PHP VALIDATION (replaces HTML5 validation)
        $errors = [];

        // Validate all fields are filled
        if (empty($currentPassword)) {
            $errors[] = 'Current password is required.';
        }
        if (empty($newPassword)) {
            $errors[] = 'New password is required.';
        }
        if (empty($confirmPassword)) {
            $errors[] = 'Password confirmation is required.';
        }

        // Validate new password length
        if (!empty($newPassword) && strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        }

        // If validation errors exist, display them and redirect
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error);
            }
            return $this->redirectToRoute('profile');
        }

        if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
            $this->addFlash('error', 'Current password incorrect.');
            return $this->redirectToRoute('profile');
        }

        if ($newPassword !== $confirmPassword) {
            $this->addFlash('error', 'Passwords do not match.');
            return $this->redirectToRoute('profile');
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        $em->flush();

        $this->addFlash('success', 'Password changed successfully.');
        return $this->redirectToRoute('profile');
    }
}
