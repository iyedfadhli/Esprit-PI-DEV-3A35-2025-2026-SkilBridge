<?php

namespace App\Controller\frontoffice\challenge;
use App\Entity\Challenge;
use App\Entity\Activity;
use App\Entity\Group;
use App\Entity\Membership;
use App\Entity\ProblemSolution;
use App\Entity\MemberActivity;
use App\Entity\User;

use App\Repository\GroupRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

final class ActivityController extends AbstractController
{

    #[Route('/activity_challenge', name: 'activity_challenge')]
    public function selectChallenges(Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('sign');
        }
        $user = $em->getRepository(User::class)->find($userId);

        $challenges = $em->getRepository(Challenge::class)->findAllOrderedByCreatedAtDesc();

        $memberships = $em->getRepository(Membership::class)->findAdminMembershipsByUser($userId);

        $groups = [];
        $userAdminGroups = [];
        $participatedGroupsByChallenge = [];
        $groupMemberBusy = [];
        foreach ($memberships as $membership) {
            $group = $membership->getGroupId();
            $userAdminGroups[] = $group->getId();
            $memberCount = $em->getRepository(Membership::class)->countMembersInGroup($group);
            $groups[] = [
                'group' => $group,
                'memberCount' => $memberCount
            ];
            $groupMemberBusy[$group->getId()] = $em->getRepository(Activity::class)->hasAnyMemberInProgressConflict($group);
        }
        $leaderGroups = $em->getRepository(Group::class)->findBy(['leaderId' => $user]);
        foreach ($leaderGroups as $lg) {
            if (!in_array($lg->getId(), $userAdminGroups, true)) {
                $userAdminGroups[] = $lg->getId();
                $memberCount = $em->getRepository(Membership::class)->countMembersInGroup($lg);
                $groups[] = [
                    'group' => $lg,
                    'memberCount' => $memberCount
                ];
                $groupMemberBusy[$lg->getId()] = $em->getRepository(Activity::class)->hasAnyMemberInProgressConflict($lg);
            }
        }
        foreach ($challenges as $challenge) {
            foreach ($groups as $g) {
                $group = $g['group'];
                $exists = $em->getRepository(Activity::class)->existsByChallengeAndGroup($challenge, $group);
                $participatedGroupsByChallenge[$challenge->getId()][$group->getId()] = $exists;
            }
        }

        return $this->render('frontoffice/challenge/activity_challenges.html.twig', [
            'challenges' => $challenges,
            'groups' => $groups,
            'userAdminGroups' => $userAdminGroups,
            'participatedGroups' => $participatedGroupsByChallenge,
            'groupMemberBusy' => $groupMemberBusy,
        ]);
    }


    #[Route('/activity/start', name: 'activity_start', methods: ['POST'])]
    public function startActivity(Request $request, EntityManagerInterface $em): Response
    {
        $challengeId = $request->request->get('challenge_id');
        $groupId = $request->request->get('group_id');

        $challenge = $em->getRepository(Challenge::class)->find($challengeId);
        $group = $em->getRepository(Group::class)->find($groupId);

        if (!$challenge || !$group) {
            throw $this->createNotFoundException('Challenge or group not found');
        }

        // Prevent duplicate participation in this challenge for the group (any status)
        $existingActivity = $em->getRepository(Activity::class)->findOneByChallengeAndGroup($challenge, $group);

        if ($existingActivity) {
            $this->addFlash('warning', 'Ce groupe participe déjà à ce challenge.');
            return $this->redirectToRoute('activity_resume', [
                'activity_id' => $existingActivity->getId(),
                'role' => 'admin', 
            ]);
        }
        // Prevent starting if any member of this group is busy in another in-progress activity
        if ($em->getRepository(Activity::class)->hasAnyMemberInProgressConflict($group)) {
            $this->addFlash('warning', 'Un membre de ce groupe est déjà engagé sur un challenge en cours.');
            return $this->redirectToRoute('activity_challenge');
        }

        // Create new activity
        $activity = new Activity();
        $activity->setIdChallenge($challenge);
        $activity->setGroupId($group);
        $activity->setStatus('in_progress');

        $em->persist($activity);
        $em->flush();
        $this->addFlash('success', 'Activité démarrée pour ce groupe.');

        return $this->redirectToRoute('activity_resume', [
            'activity_id' => $activity->getId(),
            'role' => 'admin',
        ]);
    }

    // -------------------------
    // 3️⃣ Check if user has in-progress activity
    // -------------------------
    #[Route('/activity/check', name: 'activity_check')]
    public function checkActivity(Request $request, EntityManagerInterface $em): Response
    {
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('sign');
        }

        $result = $em->createQueryBuilder()
            ->select('a', 'm.role AS memberRole')
            ->from(Activity::class, 'a')
            ->innerJoin(Group::class, 'g', 'WITH', 'a.group_id = g.id')
            ->innerJoin(Membership::class, 'm', 'WITH', 'm.group_id = g.id')
            ->where('IDENTITY(m.user_id) = :user')
            ->andWhere('a.status = :status')
            ->setParameter('user', $userId)
            ->setParameter('status', 'in_progress')
            ->setMaxResults(maxResults: 1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result) {
            $activity = $result[0];
            $role = $result['memberRole'];
            if ($role === 'leader') {
                $role = 'admin';
            }

            return $this->redirectToRoute('activity_resume', [
                'activity_id' => $activity->getId(),
                'role' => $role
            ]);
        }

        return $this->redirectToRoute('activity_challenge');
    }


    #[Route('/activity/resume', name: 'activity_resume')]
    public function resumeActivity(Request $request, EntityManagerInterface $em): Response
    {
        $activityId = $request->query->get('activity_id');
        $role = $request->query->get('role');

        if (!$activityId || !$role) {
            throw $this->createNotFoundException('Missing parameters');
        }

        $activity = $em->getRepository(Activity::class)->find($activityId);
        if (!$activity) {
            throw $this->createNotFoundException('Activity not found');
        }

        $challenge = $activity->getIdChallenge();
        $problems = $em->getRepository(ProblemSolution::class)->findByActivity($activity);
        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('sign');
        }
        $user = $em->getRepository(User::class)->find($userId);
        $memberActivityList = $em->getRepository(MemberActivity::class)
            ->findListByUserAndActivity($user, $activity);

        $memberActivity = $em->getRepository(MemberActivity::class)
            ->findOneByActivityAndUser($activity, $user);


        return $this->render('frontoffice/challenge/activity.html.twig', [
            'activity' => $activity,
            'challenge' => $challenge,
            'problems' => $problems,
            'role' => $role,
            'memberActivity' => $memberActivity,
            'memberActivityList' =>$memberActivityList,
        ]);
    }
    #[Route('/activity/submit/admin/{activity_id}', name: 'activity_submit_admin', methods: ['POST'])]
    public function submitAdmin(Request $request, EntityManagerInterface $em, int $activity_id): Response
    {
        $activity = $em->getRepository(Activity::class)->find($activity_id);
        if (!$activity) {
            throw $this->createNotFoundException('Activity not found');
        }

        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('sign');
        }
        $user = $em->getRepository(User::class)->find($userId);

        $activityDescription = $request->request->get('activity_description');
        if ($activityDescription !== null) {
            $memberActivity = $em->getRepository(MemberActivity::class)
                ->findOneByActivityAndUser($activity, $user);

            if (!$memberActivity) {
                $memberActivity = new MemberActivity();
                $memberActivity->setIdActivity($activity);
                $memberActivity->setUserId($user);
            }

            $memberActivity->setActivityDescription($activityDescription);
            $em->persist($memberActivity);
            $activity->setStatus('in_progress');
            $em->flush();
            $this->addFlash('success', 'Description d’activité enregistrée.');

            return $this->redirectToRoute('activity_resume', [
                'activity_id' => $activity->getId(),
                'role' => 'admin'
            ]);
        }

        // 1️⃣ Handle Problem Form
        $problemDescription = $request->request->get('problem_description');
        if ($problemDescription !== null && trim($problemDescription) !== '') {
            $solutionDescription = $request->request->get('solution_description');

            $problem = new ProblemSolution();
            $problem->setActivityId($activity);
            $problem->setProblemDescription($problemDescription);

            if ($solutionDescription !== null && trim($solutionDescription) !== '') {
                $problem->setGroupSolution($solutionDescription);
            }

            $em->persist($problem);
            $em->flush();
            $message = 'Problème ajouté.';
            if ($solutionDescription !== null && trim($solutionDescription) !== '') {
                $message = 'Problème et solution ajoutés.';
            }
            $this->addFlash('success', $message);
        }

        // 3️⃣ Solution form (for existing unsolved problem)
        $problemId = $request->request->get('problem_id');
        $solutionDescription = $request->request->get('solution_description');
        if ($problemId && $solutionDescription !== null && trim($solutionDescription) !== '') {
            $problem = $em->getRepository(ProblemSolution::class)->find($problemId);
            if ($problem && !$problem->getGroupSolution()) {
                $problem->setGroupSolution($solutionDescription);
                $em->persist($problem);
                $em->flush();
                $this->addFlash('success', 'Solution du groupe enregistrée.');
            }
        }

        // 3️⃣ Handle Submission File Form
        $file = $request->files->get('submission_file');
        if ($file) {
            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            $file->move($this->getParameter('CHALLENGES_UPLOAD_DIR'), $filename);
            $activity->setSubmissionFile($filename);
            $activity->setSubmissionDate(new \DateTime());
            $activity->setStatus('submitted'); // mark as submitted
            $em->flush();
            $this->addFlash('success', 'Fichier de soumission envoyé.');

            return $this->redirectToRoute('activity_check', [
                'activity_id' => $activity->getId(),
                'role' => 'admin'
            ]);
        }

        // Default redirect
        return $this->redirectToRoute('activity_resume', [
            'activity_id' => $activity->getId(),
            'role' => 'admin'
        ]);
    }


    #[Route('/activity/submit/member/{activity_id}', name: 'activity_submit_member', methods: ['POST'])]
    public function submitMember(Request $request, EntityManagerInterface $em, int $activity_id): Response
    {
        $activity = $em->getRepository(Activity::class)->find($activity_id);
        if (!$activity) {
            throw $this->createNotFoundException('Activity not found');
        }

        $userId = $request->getSession()->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('sign');
        }
        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $activityDescription = $request->request->get('activity_description');
        $problemDescription = $request->request->get('problem_description');
        $solutionDescription = $request->request->get('solution_description');
        $problemId = $request->request->get('problem_id');

        // Save member activity
        $memberActivity = $em->getRepository(MemberActivity::class)
            ->findOneBy(['user_id' => $user, 'id_activity' => $activity]);

        if (!$memberActivity) {
            $memberActivity = new MemberActivity();
            $memberActivity->setUserId($user);  
            $memberActivity->setIdActivity($activity); 
        }

        if ($activityDescription) {
            $memberActivity->setActivityDescription($activityDescription);
        }

        $em->persist($memberActivity);
        $em->flush();
        $this->addFlash('success', 'Contribution sauvegardée.');

        if ($problemDescription) {
            $problem = new ProblemSolution();
            $problem->setActivityId($activity);
            $problem->setProblemDescription($problemDescription);
            $em->persist($problem);
            $em->flush();
            $this->addFlash('success', 'Problème ajouté.');
        }

        if ($problemId && $solutionDescription !== null && trim($solutionDescription) !== '') {
            $problem = $em->getRepository(ProblemSolution::class)->find($problemId);

            if ($problem && !$problem->getGroupSolution()) {
                $problem->setGroupSolution($solutionDescription);
                $em->persist($problem);
                $em->flush();
                $this->addFlash('success', 'Solution ajoutée.');
            }
        }
        return $this->redirectToRoute('activity_resume', [
            'activity_id' => $activity->getId(),
            'role' => 'member'
        ]);
    }
    #[Route('/problem/{id}/edit', name: 'problem_edit')]
    public function editProblem(
        int $id,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $problem = $em->getRepository(ProblemSolution::class)->find($id);

        if (!$problem) {
            throw $this->createNotFoundException('Problem not found');
        }

        if ($request->isMethod('POST')) {
            $problemDescription = $request->request->get('problem_description');
            $solutionDescription = $request->request->get('solution_description');

            if ($problemDescription !== null) {
                $problem->setProblemDescription($problemDescription);
            }

            if ($solutionDescription !== null) {
                $problem->setGroupSolution($solutionDescription);
            }

            $em->flush();
            $this->addFlash('success', 'Problème mis à jour.');

            return $this->redirectToRoute('activity_check', [
                'activity_id' => $problem->getActivityId()->getId(),
                'role' => 'admin' 
            ]);
        }

        return $this->render('frontoffice/challenge/activity.html.twig', [
            'problem' => $problem
        ]);
    }
    #[Route('/problem/{id}/delete', name: 'problem_delete', methods: ['POST'])]
    public function deleteProblem(
        int $id,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $problem = $em->getRepository(ProblemSolution::class)->find($id);

        if (!$problem) {
            throw $this->createNotFoundException('Problem not found');
        }

        if (!$this->isCsrfTokenValid('delete' . $problem->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }

        $activityId = $problem->getActivityId()->getId();

        $em->remove($problem);
        $em->flush();
        $this->addFlash('success', 'Problème supprimé.');

        return $this->redirectToRoute('activity_check', [
            'activity_id' => $activityId,
        ]);
    }
    #[Route('/member/activity/delete/{id}', name: 'member_activity_delete', methods: ['POST'])]
public function deleteMemberActivity(Request $request, EntityManagerInterface $em, int $id): Response
{
    $memberActivity = $em->getRepository(MemberActivity::class)->find($id);

    if (!$memberActivity) {
        throw $this->createNotFoundException('Member activity not found.');
    }

    // CSRF token validation
    if ($this->isCsrfTokenValid('delete'.$memberActivity->getId(), $request->request->get('_token'))) {
        $em->remove($memberActivity);
        $em->flush();
            $this->addFlash('success', 'Contribution supprimée.');
    }

    // Redirect back to activity page
    return $this->redirectToRoute('activity_check', [
        'activity_id' => $memberActivity->getIdActivity()->getId(),
        'role' => 'member'
    ]);
}
#[Route('/member/activity/edit/{id}', name: 'member_activity_edit', methods: ['POST'])]
public function editMemberActivity(Request $request, EntityManagerInterface $em, int $id): Response
{
    $memberActivity = $em->getRepository(MemberActivity::class)->find($id);

    if (!$memberActivity) {
        throw $this->createNotFoundException('Member activity not found.');
    }

    // Get updated values
    $description = $request->request->get('activity_description');

    if ($description) {
        $memberActivity->setActivityDescription($description);
    }

   
    $em->flush();
    $this->addFlash('success', 'Contribution mise à jour.');

    // Redirect back to activity page
    return $this->redirectToRoute('activity_check', [
        'activity_id' => $memberActivity->getIdActivity()->getId(),
        'role' => 'member'
    ]);
}
#[Route('/activity_evaluation', name: 'activity_evaluation')]
public function index():Response{
     return $this->render('frontoffice/challenge/evaluation.html.twig');

}



}
