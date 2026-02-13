<?php

namespace App\Controller\backoffice;

use App\Entity\User;
use App\Entity\Admin;
use App\Entity\Student;
use App\Entity\Supervisor;
use App\Entity\Entreprise;

use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use TCPDF;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'dashboard')]
    public function dashboard(EntityManagerInterface $em, Request $request): Response
    {
        $userId = $request->getSession()->get('user_id');
        $user = $userId ? $em->getRepository(User::class)->find($userId) : null;

        if (!$user) {
            return $this->redirectToRoute('sign');
        }

        if ($user->getMainRoleLabel() !== 'Admin') {
            throw $this->createAccessDeniedException('Access denied');
        }

        $totalUsers = $em->getRepository(User::class)->count([]);

        return $this->render('backoffice/dashboard.html.twig', [
            'user' => $user,
            'totalUsers' => $totalUsers,
        ]);
    }

    #[Route('/user_dashboard', name: 'dashboard_user')]
    public function user(EntityManagerInterface $em, Request $request): Response
    {
        $userId = $request->getSession()->get('user_id');
        $admin = $userId ? $em->getRepository(User::class)->find($userId) : null;

        if (!$admin) {
            return $this->redirectToRoute('sign');
        }

        if ($admin->getMainRoleLabel() !== 'Admin') {
            throw $this->createAccessDeniedException('Access denied');
        }

        $search = trim((string) $request->query->get('search', ''));

        $qb = $em->getRepository(User::class)->createQueryBuilder('u');

        if ($search !== '') {
            if (ctype_digit($search)) {
                $qb->where('u.id = :id')->setParameter('id', (int) $search);
            } else {
                $qb->where('LOWER(u.nom) LIKE :q OR LOWER(u.prenom) LIKE :q OR LOWER(u.email) LIKE :q')
                   ->setParameter('q', '%' . mb_strtolower($search) . '%');
            }
        }

        $qb->orderBy('u.nom', 'ASC');
        $users = $qb->getQuery()->getResult();

        if ($request->isXmlHttpRequest()) {
            return $this->render('backoffice/user/_users_table.html.twig', [
                'users' => $users,
                'user'  => $admin,
            ]);
        }

        return $this->render('backoffice/user/user_dashboard.html.twig', [
            'users' => $users,
            'user'  => $admin,
        ]);
    }

    // ✅ ADD USER (STI) + ✅ MESSAGE EMAIL EXISTE DÉJÀ
    #[Route('/admin/users/new', name: 'admin_user_new')]
    public function addUser(
        EntityManagerInterface $em,
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $adminId = $request->getSession()->get('user_id');
        $admin = $adminId ? $em->getRepository(User::class)->find($adminId) : null;

        if (!$admin) {
            return $this->redirectToRoute('sign');
        }
        if ($admin->getMainRoleLabel() !== 'Admin') {
            throw $this->createAccessDeniedException('Access denied');
        }

        // Form sans data_class (comme tu as fait)
        $form = $this->createForm(UserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $type = (string) $form->get('type')->getData();

            // ✅ créer la bonne classe (STI)
            $newUser = match ($type) {
                'student'    => new Student(),
                'supervisor' => new Supervisor(),
                'entreprise' => new Entreprise(),
                'admin'      => new Admin(),
                default      => new Student(),
            };

            // ✅ assign fields communs
            $newUser->setNom((string) $form->get('nom')->getData());
            $newUser->setPrenom($form->get('prenom')->getData());
            $newUser->setDateNaissance($form->get('dateNaissance')->getData());
            $newUser->setEmail((string) $form->get('email')->getData());

            // ✅ domaine uniquement si entreprise (et si ton Entreprise a setDomaine)
            if ($newUser instanceof Entreprise && $form->has('domaine')) {
                $newUser->setDomaine($form->get('domaine')->getData());
            }

            // ✅ password hash
            $plainPassword = (string) $form->get('plainPassword')->getData();
            $hashed = $passwordHasher->hashPassword($newUser, $plainPassword);
            $newUser->setPassword($hashed);

            try {
                $em->persist($newUser);
                $em->flush();

                $this->addFlash('success', 'User created successfully.');
                return $this->redirectToRoute('dashboard_user');

            } catch (UniqueConstraintViolationException $e) {
                // ✅ message d'erreur sur email (au lieu HTTP 500)
                if ($form->has('email')) {
                    $form->get('email')->addError(new FormError("Cet email existe déjà."));
                } else {
                    $form->addError(new FormError("Cet email existe déjà."));
                }
            }
        }

        return $this->render('backoffice/user/new.html.twig', [
            'form' => $form->createView(),
            'user' => $admin,
        ]);
    }

    // ✅ DELETE USER
    #[Route('/admin/users/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(int $id, EntityManagerInterface $em, Request $request): Response
    {
        $adminId = $request->getSession()->get('user_id');
        $admin = $adminId ? $em->getRepository(User::class)->find($adminId) : null;

        if (!$admin) {
            return $this->redirectToRoute('sign');
        }
        if ($admin->getMainRoleLabel() !== 'Admin') {
            throw $this->createAccessDeniedException('Access denied');
        }

        $userToDelete = $em->getRepository(User::class)->find($id);
        if (!$userToDelete) {
            throw $this->createNotFoundException('User not found');
        }

        if (!$this->isCsrfTokenValid('delete_user_' . $id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $em->remove($userToDelete);
        $em->flush();

        $this->addFlash('success', 'User deleted successfully.');

        return $this->redirectToRoute('dashboard_user');
    }

    // ✅ EDIT USER (WITHOUT PASSWORD)
    #[Route('/admin/users/{id}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'])]
    public function editUser(
        int $id,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $adminId = $request->getSession()->get('user_id');
        $admin = $adminId ? $em->getRepository(User::class)->find($adminId) : null;

        if (!$admin) {
            return $this->redirectToRoute('sign');
        }
        if ($admin->getMainRoleLabel() !== 'Admin') {
            throw $this->createAccessDeniedException('Access denied');
        }

        $userToEdit = $em->getRepository(User::class)->find($id);
        if (!$userToEdit) {
            throw $this->createNotFoundException('User not found');
        }

        // Determine the current type
        $currentType = match (true) {
            $userToEdit instanceof Student => 'student',
            $userToEdit instanceof Supervisor => 'supervisor',
            $userToEdit instanceof Entreprise => 'entreprise',
            $userToEdit instanceof Admin => 'admin',
            default => 'student',
        };

        $form = $this->createForm(\App\Form\EditUserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newType = (string) $form->get('type')->getData();
            $nom = (string) $form->get('nom')->getData();
            $prenom = $form->get('prenom')->getData();
            $dateNaissance = $form->get('dateNaissance')->getData();
            $email = (string) $form->get('email')->getData();
            $domaine = $form->get('domaine')->getData();

            // Check if email is already taken by another user
            $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingUser && $existingUser->getId() !== $id) {
                $form->get('email')->addError(new FormError("Cet email est déjà utilisé."));
            } else {
                // If type changed, we need to create a new entity (STI limitation)
                if ($newType !== $currentType) {
                    $newUser = match ($newType) {
                        'student' => new Student(),
                        'supervisor' => new Supervisor(),
                        'entreprise' => new Entreprise(),
                        'admin' => new Admin(),
                        default => new Student(),
                    };

                    // Copy all common fields
                    $newUser->setNom($nom);
                    $newUser->setEmail($email);
                    $newUser->setPassword($userToEdit->getPassword()); // Keep same password
                    $newUser->setBan($userToEdit->isBan());
                    $newUser->setPhoto($userToEdit->getPhoto());
                    $newUser->setDateInscrit($userToEdit->getDateInscrit());
                    $newUser->setIsActive($userToEdit->isActive());
                    $newUser->setReportNbr($userToEdit->getReportNbr());

                    // Set type-specific fields
                    if ($newUser instanceof Student || $newUser instanceof Supervisor || $newUser instanceof Admin) {
                        $newUser->setPrenom($prenom);
                        $newUser->setDateNaissance($dateNaissance);
                    }
                    if ($newUser instanceof Entreprise && $domaine) {
                        $newUser->setDomaine($domaine);
                    }

                    $em->remove($userToEdit);
                    $em->persist($newUser);
                } else {
                    // Same type, just update fields
                    $userToEdit->setNom($nom);
                    $userToEdit->setEmail($email);

                    if ($userToEdit instanceof Student || $userToEdit instanceof Supervisor || $userToEdit instanceof Admin) {
                        $userToEdit->setPrenom($prenom);
                        $userToEdit->setDateNaissance($dateNaissance);
                    }
                    if ($userToEdit instanceof Entreprise && $domaine) {
                        $userToEdit->setDomaine($domaine);
                    }
                }

                $em->flush();

                $this->addFlash('success', 'User updated successfully.');
                return $this->redirectToRoute('dashboard_user');
            }
        }

        // Pre-fill form with current data
        if (!$form->isSubmitted()) {
            $form->get('type')->setData($currentType);
            $form->get('nom')->setData($userToEdit->getNom());
            $form->get('email')->setData($userToEdit->getEmail());

            if ($userToEdit instanceof Student || $userToEdit instanceof Supervisor || $userToEdit instanceof Admin) {
                $form->get('prenom')->setData($userToEdit->getPrenom());
                $form->get('dateNaissance')->setData($userToEdit->getDateNaissance());
            }
            if ($userToEdit instanceof Entreprise) {
                $form->get('domaine')->setData($userToEdit->getDomaine());
            }
        }

        return $this->render('backoffice/user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $admin,
            'userToEdit' => $userToEdit,
        ]);
    }

    // ✅ EXPORT USERS TO PDF
    #[Route('/admin/users/export/pdf', name: 'admin_users_export_pdf')]
    public function exportUsersPdf(EntityManagerInterface $em, Request $request): Response
    {
        $adminId = $request->getSession()->get('user_id');
        $admin = $adminId ? $em->getRepository(User::class)->find($adminId) : null;

        if (!$admin) {
            return $this->redirectToRoute('sign');
        }
        if ($admin->getMainRoleLabel() !== 'Admin') {
            throw $this->createAccessDeniedException('Access denied');
        }

        $users = $em->getRepository(User::class)->findAll();

        // Create PDF using TCPDF
        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('User Management System');
        $pdf->SetAuthor('Admin');
        $pdf->SetTitle('Users Export');
        $pdf->SetSubject('List of all users');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);

        // Add a page
        $pdf->AddPage('L'); // Landscape orientation for wide table

        // Set font
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Users Export - ' . date('Y-m-d H:i:s'), 0, 1, 'C');
        $pdf->Ln(5);

        // Table header
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(90, 123, 216);
        $pdf->SetTextColor(255, 255, 255);

        $html = '<table border="1" cellpadding="4" cellspacing="0" style="width:100%;">
            <thead>
                <tr style="background-color:#5a7bd8; color:#ffffff; font-weight:bold;">
                    <th style="width:4%;">ID</th>
                    <th style="width:10%;">Nom</th>
                    <th style="width:10%;">Prénom</th>
                    <th style="width:15%;">Email</th>
                    <th style="width:8%;">Type</th>
                    <th style="width:9%;">Date Naiss.</th>
                    <th style="width:9%;">Date Inscr.</th>
                    <th style="width:5%;">Ban</th>
                    <th style="width:10%;">Education</th>
                    <th style="width:10%;">Skills</th>
                    <th style="width:10%;">Domaine</th>
                </tr>
            </thead>
            <tbody>';

        // Table data
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(0, 0, 0);

        foreach ($users as $u) {
            $rowColor = $u->isBan() ? '#ffe6e6' : '#ffffff';
            $banStatus = $u->isBan() ? 'YES' : 'NO';
            
            $prenom = '';
            $dateNaissance = '—';
            $education = '—';
            $skills = '—';
            $domaine = '—';

            if ($u instanceof Student || $u instanceof Supervisor) {
                $prenom = htmlspecialchars($u->getPrenom() ?? '', ENT_QUOTES, 'UTF-8');
                $dateNaissance = $u->getDateNaissance() ? $u->getDateNaissance()->format('Y-m-d') : '—';
                
                if (method_exists($u, 'getEducation')) {
                    $education = htmlspecialchars($u->getEducation() ?? '—', ENT_QUOTES, 'UTF-8');
                }
                if (method_exists($u, 'getSkills')) {
                    $skills = htmlspecialchars($u->getSkills() ?? '—', ENT_QUOTES, 'UTF-8');
                }
            }

            if ($u instanceof Entreprise && method_exists($u, 'getDomaine')) {
                $domaine = htmlspecialchars($u->getDomaine() ?? '—', ENT_QUOTES, 'UTF-8');
            }

            $html .= '<tr style="background-color:' . $rowColor . ';">
                <td style="width:4%;">' . $u->getId() . '</td>
                <td style="width:10%;">' . htmlspecialchars($u->getNom(), ENT_QUOTES, 'UTF-8') . '</td>
                <td style="width:10%;">' . $prenom . '</td>
                <td style="width:15%;">' . htmlspecialchars($u->getEmail(), ENT_QUOTES, 'UTF-8') . '</td>
                <td style="width:8%;">' . $u->getMainRoleLabel() . '</td>
                <td style="width:9%;">' . $dateNaissance . '</td>
                <td style="width:9%;">' . $u->getDateInscrit()->format('Y-m-d') . '</td>
                <td style="width:5%;">' . $banStatus . '</td>
                <td style="width:10%;">' . substr($education, 0, 50) . '</td>
                <td style="width:10%;">' . substr($skills, 0, 50) . '</td>
                <td style="width:10%;">' . substr($domaine, 0, 50) . '</td>
            </tr>';
        }

        $html .= '</tbody></table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        // Output PDF
        $pdfContent = $pdf->Output('users_export_' . date('Y-m-d') . '.pdf', 'S');

        return new Response(
            $pdfContent,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="users_export_' . date('Y-m-d') . '.pdf"',
            ]
        );
    }
}
