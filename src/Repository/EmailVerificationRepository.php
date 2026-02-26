<?php

namespace App\Repository;

use App\Entity\EmailVerification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailVerification>
 */
class EmailVerificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailVerification::class);
    }

    /**
     * Find the latest non-expired OTP for a given email.
     */
    public function findValidCode(string $email, string $code): ?EmailVerification
    {
        return $this->createQueryBuilder('ev')
            ->where('ev.email = :email')
            ->andWhere('ev.code = :code')
            ->andWhere('ev.expiresAt > :now')
            ->setParameter('email', $email)
            ->setParameter('code', $code)
            ->setParameter('now', new \DateTime())
            ->orderBy('ev.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Remove all OTP codes for a given email.
     */
    public function removeAllForEmail(string $email): void
    {
        $this->createQueryBuilder('ev')
            ->delete()
            ->where('ev.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->execute();
    }
}
