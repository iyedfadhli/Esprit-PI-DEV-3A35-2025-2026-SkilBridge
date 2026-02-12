<?php

namespace App\Controller\frontoffice\User;

use App\Entity\User;
use App\Entity\Student;
use App\Entity\Supervisor;
use App\Entity\Entreprise;
use App\Entity\Admin;

use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Form\LoginType;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;

final class UserController extends AbstractController
{
    #[Route(path: '/sign_up_in', name: 'sign')]
    #[Route(path: '/sign_up_in', name: 'sign')]
    public function signUpIn(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($request->request->has('register_submit')) {
            $type = $request->request->get('type');
            $prenom = $request->request->get('prenom');
            $nom = $request->request->get('nom');
            $dateNaissance = $request->request->get('date_naissance');
            $email = $request->request->get('email');
            $domaine = $request->request->get('domaine');
            $passwd = $request->request->get('passwd');

            if ($type === 'student') {
                $user = new Student();
                $user->setPrenom($prenom);
                $user->setDateNaissance(new \DateTime($dateNaissance));
            } elseif ($type === 'supervisor') {
                $user = new Supervisor();
                $user->setPrenom($prenom);
                $user->setDateNaissance(new \DateTime($dateNaissance));
            } elseif ($type === 'entreprise') {
                $user = new Entreprise();
                $user->setDomaine($domaine);
            } else {
                $this->addFlash('error', 'Invalid account type');
                return $this->redirectToRoute('sign');
            }

            $user->setNom($nom);
            $user->setEmail($email);
            $hashedPassword = $passwordHasher->hashPassword($user, $passwd);
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Account created successfully!');
            return $this->redirectToRoute('sign');
        }

        $loginForm = $this->createForm(LoginType::class);
        $loginForm->handleRequest($request);

        if ($loginForm->isSubmitted() && $loginForm->isValid()) {
            $data = $loginForm->getData();
            $email = $data->getEmail();
            $password = $data->getPassword();

            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
                $this->addFlash('error', 'Invalid email or password');
            } else {
                $request->getSession()->set('user_id', $user->getId());
                $this->addFlash('success', 'Logged in successfully!');
                return $this->redirectToRoute('home');
            }
        }

        return $this->render('frontoffice/user/sign.html.twig', [
            'form' => $loginForm->createView(),
        ]);
    }
    #[Route('/logout', name: 'logout')]
    public function logout(Request $request): Response
    {
        $request->getSession()->remove('user_id');
        $request->getSession()->invalidate();
        return $this->redirectToRoute('home');
    }
    #[Route('/profile', name: 'profile')]
    public function profile(Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        $user = $em->getRepository(User::class)->find($userId);

        if (!$user) {
            return $this->redirectToRoute('sign');
        }

        return $this->render('frontoffice/user/profile.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/profile/edit/{id}', name: 'profile_edit')]
    public function editProfile(Request $request, EntityManagerInterface $em, User $user): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request;

            $user->setNom($data->get('nom'));
            $user->setEmail($data->get('email'));

            if ($user instanceof Student) {
                $user->setPrenom($data->get('prenom'));
                $user->setEducation($data->get('education'));
                $user->setSkills($data->get('skills'));
            } elseif ($user instanceof Supervisor) {
                $user->setPrenom($data->get('prenom'));
                $user->setExperience($data->get('experience'));
            } elseif ($user instanceof Entreprise) {
                $user->setDomaine($data->get('domaine'));
            } elseif ($user instanceof Admin) {
                $user->setPrenom($data->get('prenom'));
            }

            $em->flush();

            $this->addFlash('success', 'Profile updated successfully!');
            return $this->redirectToRoute('profile_edit', ['id' => $user->getId()]);
        }

        return $this->render('frontoffice/user/profile.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/profile/change-password/{id}', name: 'profile_change_password', methods: ['GET', 'POST'])]
    public function changePassword(
        int $id,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        LogoutUrlGenerator $logoutUrlGenerator
    ): Response {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('profile');
        }


        if ($request->isMethod('POST')) {
            $currentPassword = $request->request->get('current_password');
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Current password is incorrect.');
                return $this->redirectToRoute('profile');
            }

            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'New passwords do not match.');
                return $this->redirectToRoute('profile');
            }

            // Hash and update password
            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $em->flush();

            $this->addFlash('success', 'Password changed successfully. Please log in again.');

            return $this->redirectToRoute('profile');
        }

        return $this->render('frontoffice/user/profile.html.twig');
    }

    #[Route('/profile/change-photo/{id}', name: 'profile_change_photo', methods: ['POST'])]
    public function changePhoto(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('profile');
        }

        $file = $request->files->get('profile');

        if ($file) {
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/assets/images/frontoffice/user_pic';
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower($user->getNom()));
            $filename = $safeName . '.' . $file->guessExtension();

            $file->move($uploadsDir, $filename);

            $user->setPhoto('assets/images/frontoffice/user_pic/' . $filename);
            $em->flush();

            $this->addFlash('success', 'Profile picture updated!');
        } else {
            $this->addFlash('error', 'No file selected.');
        }

        return $this->redirectToRoute('profile');
    }

    #[Route('/user/{id}/ban', name: 'user_ban')]
    public function ban(User $user, EntityManagerInterface $em): RedirectResponse
    {
        if ($user === $this->getUser()) {
            $this->addFlash('error', 'You cannot ban yourself!');
            return $this->redirectToRoute('dashboard_user');
        }

        $user->setBan(true);
        $em->flush();

        $this->addFlash('success', $user->getNom() . ' has been banned.');
        return $this->redirectToRoute('dashboard_user');
    }

    #[Route('/user/{id}/unban', name: 'user_unban')]
    public function unban(User $user, EntityManagerInterface $em): RedirectResponse
    {
        $user->setBan(false);
        $em->flush();

        $this->addFlash('success', $user->getNom() . ' has been unbanned.');
        return $this->redirectToRoute('dashboard_user');
    }
    #[Route('/user/{id}/delete', name: 'user_delete')]
    public function delete(User $user, EntityManagerInterface $em): RedirectResponse
    {
        if ($user === $this->getUser()) {
            $this->addFlash('error', 'You cannot delete yourself!');
            return $this->redirectToRoute('dashboard_user');
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', $user->getNom() . ' has been deleted.');
        return $this->redirectToRoute('dashboard_user');
    }
    #[Route('/user/{id}/promote-admin', name: 'user_promote_admin')]
    public function promoteAdmin(User $user, EntityManagerInterface $em): Response
    {
        // Only promote if the user is not already an Admin
        if ($user->getMainRoleLabel() !== 'Admin') {
            // Store the current role (lowercase type) as previousRole
            $currentRole = strtolower($user->getMainRoleLabel());
            $user->setPreviousRole($currentRole);
            $em->flush(); // Save the previous role before updating type

            // Update the 'type' column directly to admin
            $em->getConnection()->executeStatement(
                'UPDATE user SET type = :type WHERE id = :id',
                ['type' => 'admin', 'id' => $user->getId()]
            );
        }

        return $this->redirectToRoute('dashboard_user');
    }
    #[Route('/user/{id}/demote-admin', name: 'user_demote_admin')] public function demoteAdmin(User $user, EntityManagerInterface $em): Response
    {
        $previousRole = $user->getPreviousRole();
        if ($user->getMainRoleLabel() === 'Admin' && $previousRole) {
            $em->getConnection()->executeStatement('UPDATE user SET type = :type WHERE id = :id', ['type' => $previousRole, 'id' => $user->getId()]);
            $user->setPreviousRole(null);
            $em->flush();
        }
        return $this->redirectToRoute('dashboard_user');
    }


}






