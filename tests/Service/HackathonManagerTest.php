<?php

namespace App\Tests\Service;

use App\Entity\Hackathon;
use App\Service\HackathonManager;
use PHPUnit\Framework\TestCase;

class HackathonManagerTest extends TestCase
{
    public function testValidHackathon(): void
    {
        $hackathon = $this->createValidHackathon();
        $manager = new HackathonManager();

        $this->assertTrue($manager->validate($hackathon));
    }

    public function testHackathonWithoutTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $hackathon = $this->createValidHackathon();
        $hackathon->setTitle('');

        $manager = new HackathonManager();
        $manager->validate($hackathon);
    }

    public function testHackathonWithoutTheme(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $hackathon = $this->createValidHackathon();
        $hackathon->setTheme('');

        $manager = new HackathonManager();
        $manager->validate($hackathon);
    }

    public function testHackathonWithEndBeforeStart(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $hackathon = $this->createValidHackathon();
        $hackathon->setStartAt(new \DateTimeImmutable('2026-04-10 10:00:00'));
        $hackathon->setEndAt(new \DateTimeImmutable('2026-04-10 09:00:00'));

        $manager = new HackathonManager();
        $manager->validate($hackathon);
    }

    public function testHackathonWithLateRegistrationClose(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $hackathon = $this->createValidHackathon();
        $hackathon->setStartAt(new \DateTimeImmutable('2026-04-10 10:00:00'));
        $hackathon->setRegistrationCloseAt(new \DateTimeImmutable('2026-04-10 10:00:00'));

        $manager = new HackathonManager();
        $manager->validate($hackathon);
    }

    public function testHackathonWithNegativeFee(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $hackathon = $this->createValidHackathon();
        $hackathon->setFee(-1.0);

        $manager = new HackathonManager();
        $manager->validate($hackathon);
    }

    private function createValidHackathon(): Hackathon
    {
        $hackathon = new Hackathon();
        $hackathon->setTitle('AI Hackathon');
        $hackathon->setTheme('Artificial Intelligence');
        $hackathon->setDescription('Build innovative AI solutions.');
        $hackathon->setRules('Respect rules and submit before deadline.');
        $hackathon->setStartAt(new \DateTimeImmutable('2026-04-10 10:00:00'));
        $hackathon->setEndAt(new \DateTimeImmutable('2026-04-12 18:00:00'));
        $hackathon->setRegistrationOpenAt(new \DateTime('2026-03-20 09:00:00'));
        $hackathon->setRegistrationCloseAt(new \DateTimeImmutable('2026-04-09 23:00:00'));
        $hackathon->setFee(0.0);
        $hackathon->setMaxTeams(20);
        $hackathon->setTeamSizeMax(4);
        $hackathon->setLocation('Tunis');
        $hackathon->setCoverUrl('cover.jpg');
        $hackathon->setStatus('OPEN');

        return $hackathon;
    }
}
