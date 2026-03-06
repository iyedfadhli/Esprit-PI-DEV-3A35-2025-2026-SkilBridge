<?php

namespace App\Service;

use App\Entity\User;

class UserManager
{
    public function validate(User $user): bool
    {
        if ($user->getNom() === null || trim($user->getNom()) === '') {
            throw new \InvalidArgumentException('User name is required.');
        }

        if (mb_strlen($user->getNom()) < 2) {
            throw new \InvalidArgumentException('User name must be at least 2 characters.');
        }

        if ($user->getEmail() === null || !filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('User email is invalid.');
        }

        if (mb_strlen($user->getPassword()) < 6) {
            throw new \InvalidArgumentException('User password must be at least 6 characters.');
        }

        if ($user->getReportNbr() < 0) {
            throw new \InvalidArgumentException('User report number cannot be negative.');
        }

        return true;
    }
}
