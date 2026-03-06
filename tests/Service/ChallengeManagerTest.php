<?php

namespace App\Tests\Service;

use App\Entity\Challenge;
use App\Entity\Course;
use App\Entity\User;
use App\Service\ChallengeManager;
use PHPUnit\Framework\TestCase;

class ChallengeManagerTest extends TestCase
{
    public function testValidChallenge(): void
    {
        $challenge = $this->createValidChallenge();
        $manager = new ChallengeManager();

        $this->assertTrue($manager->validate($challenge));
    }

    public function testChallengeWithoutTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $challenge = $this->createValidChallenge();
        $challenge->setTitle('');

        $manager = new ChallengeManager();
        $manager->validate($challenge);
    }

    public function testChallengeWithoutDescription(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $challenge = $this->createValidChallenge();
        $challenge->setDescription('');

        $manager = new ChallengeManager();
        $manager->validate($challenge);
    }

    public function testChallengeWithInvalidMinGroupNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $challenge = $this->createValidChallenge();
        $challenge->setMinGroupNbr(0);

        $manager = new ChallengeManager();
        $manager->validate($challenge);
    }

    public function testChallengeWithMaxGroupLowerThanMinGroup(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $challenge = $this->createValidChallenge();
        $challenge->setMinGroupNbr(5);
        $challenge->setMaxGroupNbr(3);

        $manager = new ChallengeManager();
        $manager->validate($challenge);
    }

    public function testChallengeWithDeadlineBeforeCreationDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $challenge = $this->createValidChallenge();
        $challenge->setCreatedAt(new \DateTime('2026-03-10 12:00:00'));
        $challenge->setDeadLine(new \DateTime('2026-03-10 11:00:00'));

        $manager = new ChallengeManager();
        $manager->validate($challenge);
    }

    private function createValidChallenge(): Challenge
    {
        $challenge = new Challenge();
        $challenge->setTitle('Symfony Challenge');
        $challenge->setDescription('Build unit tests for challenge logic.');
        $challenge->setTargetSkill('Testing');
        $challenge->setDifficulty('INTERMEDIATE');
        $challenge->setMinGroupNbr(2);
        $challenge->setMaxGroupNbr(5);
        $challenge->setCreatedAt(new \DateTime('2026-03-10 10:00:00'));
        $challenge->setDeadLine(new \DateTime('2026-03-12 10:00:00'));
        $challenge->setCreator(new User());
        $challenge->setCourse(new Course());

        return $challenge;
    }
}
