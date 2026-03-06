<?php

namespace App\Tests\Service;

use App\Entity\Cv;
use App\Entity\User;
use App\Service\CvManager;
use PHPUnit\Framework\TestCase;

class CvManagerTest extends TestCase
{
    public function testValidCv(): void
    {
        $cv = $this->createValidCv();
        $manager = new CvManager();

        $this->assertTrue($manager->validate($cv));
    }

    public function testCvWithoutName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $cv = $this->createValidCv();
        $cv->setNomCv('');

        $manager = new CvManager();
        $manager->validate($cv);
    }

    public function testCvWithInvalidLanguage(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $cv = $this->createValidCv();
        $cv->setLangue('Espagnol');

        $manager = new CvManager();
        $manager->validate($cv);
    }

    public function testCvWithInvalidProgression(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $cv = $this->createValidCv();
        $cv->setProgression(120);

        $manager = new CvManager();
        $manager->validate($cv);
    }

    public function testCvWithoutCreationDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $cv = new Cv();
        $cv->setNomCv('My CV');
        $cv->setLangue('Francais');
        $cv->setProgression(50);
        $cv->setUser(new User());

        $manager = new CvManager();
        $manager->validate($cv);
    }

    public function testCvWithoutUser(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $cv = $this->createValidCv();
        $cv->setUser(null);

        $manager = new CvManager();
        $manager->validate($cv);
    }

    private function createValidCv(): Cv
    {
        $cv = new Cv();
        $cv->setNomCv('My CV');
        $cv->setLangue('Francais');
        $cv->setProgression(50);
        $cv->setCreationDate(new \DateTime('2026-03-10 10:00:00'));
        $cv->setUser(new User());

        return $cv;
    }
}
