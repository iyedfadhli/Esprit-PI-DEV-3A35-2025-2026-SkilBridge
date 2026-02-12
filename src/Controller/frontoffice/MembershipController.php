<?php

namespace App\Controller\frontoffice;

use App\Entity\Group;
use App\Entity\Membership;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MembershipController extends AbstractController
{
    #[Route('/groups/{id}/join', name: 'group_join')]
    public function join(Group $group, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Check if already a member
        $existing = $em->getRepository(Membership::class)->findOneBy([
            'user_id' => $user,
            'group_id' => $group,
        ]);

        if (!$existing) {
            $membership = new Membership();
            $membership->setUserId($user);
            $membership->setGroupId($group);
            $membership->setRole('member');
            $membership->setContributionScore(0);
            $membership->setIsActive(true);

            $em->persist($membership);
            $em->flush();
        }

        return $this->redirectToRoute('group_show', ['id' => $group->getId()]);
    }

    #[Route('/groups/{id}/leave', name: 'group_leave')]
    public function leave(Group $group, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $membership = $em->getRepository(Membership::class)->findOneBy([
            'user_id' => $user,
            'group_id' => $group,
        ]);

        if ($membership) {
            $em->remove($membership);
            $em->flush();
        }

        return $this->redirectToRoute('groups_index');
    }

    #[Route('/groups/{id}/add-member', name: 'group_add_member')]
public function addMember(Request $request, Group $group, EntityManagerInterface $em): Response
{
    // Search by username (from form input)
    $searchTerm = $request->query->get('q');
    $users = [];

    if ($searchTerm) {
        $users = $em->getRepository(User::class)->createQueryBuilder('u')
            ->where('u.username LIKE :search')
            ->setParameter('search', '%' . $searchTerm . '%')
            ->getQuery()
            ->getResult();
    }

    return $this->render('frontoffice/groups/add_member.html.twig', [
        'group' => $group,
        'users' => $users,
    ]);
}

#[Route('/groups/{groupId}/add-member/{userId}', name: 'group_add_member_confirm')]
    public function addMemberConfirm(int $groupId, int $userId, EntityManagerInterface $em): Response
    {
        $group = $em->getRepository(Group::class)->find($groupId);
        $user = $em->getRepository(User::class)->find($userId);

        // Check for duplicates
        $existing = $em->getRepository(Membership::class)->findOneBy([
            'user_id' => $user,
            'group_id' => $group,
        ]);

        if ($existing) {
             // Maybe add a flash message here
             return $this->redirectToRoute('group_show', ['id' => $groupId]);
        }

        $membership = new Membership();
        $membership->setUserId($user);
        $membership->setGroupId($group);
        $membership->setRole('member'); // default role
        $membership->setContributionScore(0);
        $membership->setIsActive(true);

        $em->persist($membership);
        $em->flush();

        return $this->redirectToRoute('group_show', ['id' => $groupId]);
    }

    #[Route('/groups/{groupId}/kick/{membershipId}', name: 'group_kick_member')]
    public function kick(int $groupId, int $membershipId, EntityManagerInterface $em): Response
    {
        $membership = $em->getRepository(Membership::class)->find($membershipId);
        
        if ($membership) {
            $em->remove($membership);
            $em->flush();
        }

        return $this->redirectToRoute('group_show', ['id' => $groupId]);
    }

#[Route('/groups/{groupId}/set-role/{membershipId}/{role}', name: 'group_set_role')]
public function setRole(int $groupId, int $membershipId, string $role, EntityManagerInterface $em): Response
{
    $membership = $em->getRepository(Membership::class)->find($membershipId);
    $membership->setRole($role);

    $em->flush();

    return $this->redirectToRoute('group_show', ['id' => $groupId]);
}
}