<?php

namespace App\Controller\frontoffice\challenge;
use App\Entity\Challenge;
use App\Entity\Activity;
use App\Entity\Group;
use App\Entity\Membership;
use App\Entity\ProblemSolution;
use App\Entity\MemberActivity;
use App\Entity\User;
use App\Entity\Evaluation;
use Symfony\Component\HttpFoundation\File\File;

use App\Repository\GroupRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

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

        $challenges = $em->getRepository(Challenge::class)->findAllOrderedByCreatedAtDesc(99);

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
    public function startActivity(Request $request, EntityManagerInterface $em, HttpClientInterface $http): Response
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

        // Optional KB upsert if configured (works with Qdrant/PGVector KB)
        $pdfPublicPath = $challenge->getContent();
        if ($pdfPublicPath) {
            $filenameBase = basename($pdfPublicPath);
            $uploadDir = rtrim($this->getStringParameter('CHALLENGES_UPLOAD_DIR'), "/\\");
            $uploadAbs = $uploadDir . DIRECTORY_SEPARATOR . $filenameBase;
            $publicAbs = $this->getStringParameter('kernel.project_dir') . '/public/' . ltrim($pdfPublicPath, '/');
            $existingAbs = null;
            if (file_exists($uploadAbs)) {
                $existingAbs = $uploadAbs;
            } elseif (file_exists($publicAbs)) {
                $existingAbs = $publicAbs;
            }
            if ($existingAbs && is_readable($existingAbs)) {
                $metadata = json_encode(['challenge_id' => (string) $challenge->getId()]);
                $additionalMetadata = json_encode(['challenge_id' => (string) $challenge->getId()]);
                $formData = new FormDataPart([
                    'files' => DataPart::fromPath($existingAbs, $filenameBase, 'application/pdf'),
                    'metadata' => $metadata === false ? '{}' : $metadata,
                    'additionalMetadata' => $additionalMetadata === false ? '{}' : $additionalMetadata,
                    'returnList' => 'true',
                ]);
                $headers = $formData->getPreparedHeaders()->toArray();
                try {
                    $resp = $http->request('POST', 'http://localhost:3000/api/v1/vector/upsert/556a4e54-5118-47a8-bf43-c080b3951fc5', [
                        'headers' => $headers,
                        'body' => $formData->bodyToString(),
                    ]);
                    $status = $resp->getStatusCode();
                    if ($status !== 200 && $status !== 201) {
                        $this->addFlash('error', 'KB upsert failed');
                    }
                } catch (\Throwable $e) {
                    $this->addFlash('error', 'KB upsert error: ' . $e->getMessage());
                }
            }
        }

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
            ->innerJoin(Membership::class, 'm', 'WITH', 'm.group_id = a.group_id')
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
            'memberActivityList' => $memberActivityList,
        ]);
    }
    #[Route('/api/activity/{activity_id}/chat', name: 'activity_chat', methods: ['POST'])]
    public function chatActivity(int $activity_id, Request $request, EntityManagerInterface $em, HttpClientInterface $http): Response
    {
        $activity = $em->getRepository(Activity::class)->find($activity_id);
        if (!$activity) {
            return $this->json(['error' => 'Activity not found'], 404);
        }

        $payload = json_decode($request->getContent(), true) ?? [];
        $question = isset($payload['question']) ? (string) $payload['question'] : '';
        if ($question === '') {
            return $this->json(['error' => 'Missing question'], 400);
        }

        $challengeId = isset($payload['challenge_id']) && $payload['challenge_id'] !== ''
            ? (string) $payload['challenge_id']
            : '67';

        $metadataFilter = 'challenge_id:"' . $challengeId . '"';

        $userId = $payload['user_id'] ?? 'anonymous';
        $sessionId = "challenge_{$challengeId}_user_{$userId}";

        try {
            $headers = [];
            $apiKeyParam = $this->getParameter('FLOWISE_API_KEY');
            $apiKey = is_string($apiKeyParam) ? $apiKeyParam : '';
            if ($apiKey !== '') {
                $headers['Authorization'] = 'Bearer ' . $apiKey; // REQUIRED
                $headers['X-API-Key'] = $apiKey; // Optional, some versions of Flowise need this
            } else {
                throw new \RuntimeException("FLOWISE_API_KEY is not set in parameters.yaml");
            }
            $headers['Accept'] = 'application/json';


            $pdfPublicPath = $activity->getIdChallenge()->getContent();
            $fileUrl = null;
            if (is_string($pdfPublicPath) && $pdfPublicPath !== '') {
                $fileUrl = rtrim((string) $request->getSchemeAndHttpHost(), '/') . '/' . ltrim($pdfPublicPath, '/');
            }


            $resp = $http->request('POST', 'http://localhost:3000/api/v1/prediction/556a4e54-5118-47a8-bf43-c080b3951fc5', [
                'headers' => $headers,
                'json' => [
                    'question' => $question,
                    'session_id' => $sessionId,
                    'grade_activity_TEST_' . time() .
                    'additional_parameters' => [
                        'temperature' => 0,
                        'max_tokens' => 500
                    ],
                    'overrideConfig' => [
                        'fileUrl' => $fileUrl,
                        'challenge_id' => $challengeId
                    ]
                ],
            ]);

            $status = $resp->getStatusCode();
            $body = $resp->getContent(false);
            $data = json_decode($body, true);

            if ($status !== 200 && $status !== 201) {
                return $this->json(['error' => $data ?? $body], $status);
            }

            $headersResp = $resp->getHeaders(false);
            $contentType = $headersResp['content-type'][0] ?? '';
            if (stripos($contentType, 'text/html') !== false) {
                return $this->json([
                    'error' => 'Flowise returned HTML instead of JSON. Verify chatflow ID and API key; ensure API endpoint /api/v1/chatflows/{id} is correct.',
                    'raw' => $body
                ], 502);
            }

            $text = '';
            $sources = [];
            if (is_array($data)) {
                foreach ($data as $item) {
                    if (is_array($item)) {
                        $text = $item['text'] ?? $item['answer'] ?? $text;
                    }
                }
            }
            $stack = is_array($data) ? [$data] : [];
            while (!empty($stack)) {
                $current = array_pop($stack);
                if (isset($current['sourceDocuments']) && is_array($current['sourceDocuments'])) {
                    $sources = $current['sourceDocuments'];
                    break;
                }
                if (isset($current['sources']) && is_array($current['sources'])) {
                    $sources = $current['sources'];
                    break;
                }
                foreach ($current as $v) {
                    if (is_array($v)) {
                        $stack[] = $v;
                    }
                }
            }

            $validSources = [];
            foreach ($sources as $s) {
                if (is_array($s)) {
                    $m = $s['metadata'] ?? $s['meta'] ?? null;
                    if (is_array($m)) {
                        $cid = $m['challenge_id'] ?? ($m['challengeId'] ?? null);
                        if ($cid !== null && (string) $cid === (string) $challengeId) {
                            $validSources[] = $s;
                        }
                    }
                }
            }


            return $this->json([
                'text' => $text,
                'sources' => $validSources,
                'raw' => $data
            ]);

        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
    #[Route('/activity/submit/admin/{activity_id}', name: 'activity_submit_admin', methods: ['POST'])]
    public function submitAdmin(
        Request $request,
        EntityManagerInterface $em,
        \App\Service\NotificationService $notifier,
        HttpClientInterface $http,
        \App\Service\FlowiseGraderService $graderService,
        int $activity_id
    ): Response {
        $activity = $em->getRepository(Activity::class)->find($activity_id);
        if (!$activity)
            throw $this->createNotFoundException('Activity not found');

        $userId = $request->getSession()->get('user_id');
        if (!$userId)
            return $this->redirectToRoute('sign');
        $user = $em->getRepository(User::class)->find($userId);

        $activityDescription = $this->getStringInput($request, 'activity_description');
        $problemDescription = $this->getStringInput($request, 'problem_description');
        $solutionDescription = $this->getStringInput($request, 'solution_description');
        $problemId = $request->request->get('problem_id');
        if ($activityDescription !== null) {
            $memberActivity = $em->getRepository(MemberActivity::class)->findOneByActivityAndUser($activity, $user) ?? new MemberActivity();
            $memberActivity->setIdActivity($activity);
            $memberActivity->setUserId($user);
            $memberActivity->setActivityDescription($activityDescription);
            $em->persist($memberActivity);
            $activity->setStatus('in_progress');
            $em->flush();
            return $this->redirectToRoute('activity_resume', ['activity_id' => $activity->getId(), 'role' => 'admin']);
        }

        if ($problemDescription) {
            $problem = new ProblemSolution();
            $problem->setActivityId($activity);
            $problem->setProblemDescription($problemDescription);
            $em->persist($problem);
            $em->flush();
            $this->addFlash('success', 'ProblÃ¨me ajoutÃ©.');
            return $this->redirectToRoute('activity_resume', ['activity_id' => $activity->getId(), 'role' => 'admin']);
        }

        if ($problemId && $solutionDescription !== null && trim($solutionDescription) !== '') {
            $problem = $em->getRepository(ProblemSolution::class)->find($problemId);
            if ($problem && !$problem->getGroupSolution()) {
                $problem->setGroupSolution($solutionDescription);
                $em->persist($problem);
                $em->flush();
                $this->addFlash('success', 'Solution ajoutÃ©e.');
            }
            return $this->redirectToRoute('activity_resume', ['activity_id' => $activity->getId(), 'role' => 'admin']);
        }

        $file = $request->files->get('submission_file');
        if ($file) {
            $challenge = $activity->getIdChallenge();
            $group = $activity->getGroupId();

            $filename = md5(uniqid()) . '.' . $file->guessExtension();
            $uploadDirAbs = rtrim($this->getStringParameter('CHALLENGES_UPLOAD_DIR'), DIRECTORY_SEPARATOR);
            $file->move($uploadDirAbs, $filename);

            $activity->setSubmissionFile($filename);
            $activity->setSubmissionDate(new \DateTime());
            $activity->setStatus('submitted');
            $em->flush();

            try {
                $projectDir = $this->getStringParameter('kernel.project_dir');
                $challengeContent = $challenge->getContent();
                $challengePath = $projectDir . '/public/' . ltrim($challengeContent ?? '', '/');
                $submissionPath = $uploadDirAbs . DIRECTORY_SEPARATOR . $filename;
                $appUrl = (string) $request->getSchemeAndHttpHost();
                $apiKeyParam = $this->getParameter('FLOWISE_API_KEY');
                $apiKey = is_string($apiKeyParam) ? $apiKeyParam : '';

                $grade = $graderService->gradeByTextExtraction($challengePath, $submissionPath, $appUrl, $apiKey);

                $evaluation = $em->getRepository(Evaluation::class)->findOneBy(['activity_id' => $activity]) ?? new \App\Entity\Evaluation();
                $evaluation->setActivityId($activity);
                $evaluation->setGroupScore(min(20, max(0, (float) ($grade['overall_score'] ?? 0))));
                $preFeedback = json_encode($grade, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $evaluation->setPreFeedback($preFeedback === false ? null : $preFeedback);

                $em->persist($evaluation);
                $em->flush();

                $this->addFlash('success', 'Graded successfully. Score: ' . $evaluation->getGroupScore());

            } catch (\Throwable $e) {
                $this->addFlash('error', 'Grading failed: ' . $e->getMessage());
            }

            return $this->redirectToRoute('activity_check', ['activity_id' => $activity->getId(), 'role' => 'admin']);
        }

        return $this->redirectToRoute('activity_resume', ['activity_id' => $activity->getId(), 'role' => 'admin']);
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

        $activityDescription = $this->getStringInput($request, 'activity_description');
        $problemDescription = $this->getStringInput($request, 'problem_description');
        $solutionDescription = $this->getStringInput($request, 'solution_description');
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
            $problemDescription = $this->getStringInput($request, 'problem_description');
            $solutionDescription = $this->getStringInput($request, 'solution_description');

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

        $csrfToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete' . $problem->getId(), is_string($csrfToken) ? $csrfToken : null)) {
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
        $csrfToken = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $memberActivity->getId(), is_string($csrfToken) ? $csrfToken : null)) {
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
        $description = $this->getStringInput($request, 'activity_description');

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
    public function index(): Response
    {
        return $this->render('frontoffice/challenge/evaluation.html.twig');

    }

    private function getStringInput(Request $request, string $key): ?string
    {
        $value = $request->request->get($key);
        return is_string($value) ? $value : null;
    }

    private function getStringParameter(string $key): string
    {
        $value = $this->getParameter($key);
        return is_string($value) ? $value : '';
    }



}
