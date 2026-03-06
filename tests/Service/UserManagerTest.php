<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\UserManager;
use PHPUnit\Framework\TestCase;

class UserManagerTest extends TestCase
{
    public function testValidUser(): void
    {
        $user = $this->createValidUser();
        $manager = new UserManager();

        $this->assertTrue($manager->validate($user));
    }

    public function testUserWithoutName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = $this->createValidUser();
        $user->setNom('');

        $manager = new UserManager();
        $manager->validate($user);
    }

    public function testUserWithTooShortName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = $this->createValidUser();
        $user->setNom('A');

        $manager = new UserManager();
        $manager->validate($user);
    }

    public function testUserWithInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = $this->createValidUser();
        $user->setEmail('invalid-email');

        $manager = new UserManager();
        $manager->validate($user);
    }

    public function testUserWithShortPassword(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = $this->createValidUser();
        $user->setPassword('12345');

        $manager = new UserManager();
        $manager->validate($user);
    }

    public function testUserWithNegativeReportNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $user = $this->createValidUser();
        $user->setReportNbr(-1);

        $manager = new UserManager();
        $manager->validate($user);
    }

    private function createValidUser(): User
    {
        $user = new User();
        $user->setNom('Doe');
        $user->setPrenom('John');
        $user->setEmail('john.doe@example.com');
        $user->setPassword('secret123');
        $user->setReportNbr(0);

        return $user;
    }
}
