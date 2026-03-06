<?php

namespace App\Service;

use App\Entity\Hackathon;

class HackathonManager
{
    public function validate(Hackathon $hackathon): bool
    {
        if ($hackathon->getTitle() === null || trim($hackathon->getTitle()) === '') {
            throw new \InvalidArgumentException('Hackathon title is required.');
        }

        if ($hackathon->getTheme() === null || trim($hackathon->getTheme()) === '') {
            throw new \InvalidArgumentException('Hackathon theme is required.');
        }

        if ($hackathon->getStartAt() === null || $hackathon->getEndAt() === null || $hackathon->getEndAt() <= $hackathon->getStartAt()) {
            throw new \InvalidArgumentException('Hackathon end date must be after start date.');
        }

        if ($hackathon->getRegistrationCloseAt() === null || $hackathon->getStartAt() === null || $hackathon->getRegistrationCloseAt() >= $hackathon->getStartAt()) {
            throw new \InvalidArgumentException('Hackathon registration must close before start date.');
        }

        if ($hackathon->getFee() === null || (float) $hackathon->getFee() < 0) {
            throw new \InvalidArgumentException('Hackathon fee cannot be negative.');
        }

        if ($hackathon->getMaxTeams() === null || $hackathon->getMaxTeams() < 1 || $hackathon->getTeamSizeMax() === null || $hackathon->getTeamSizeMax() < 1) {
            throw new \InvalidArgumentException('Hackathon team limits must be at least 1.');
        }

        return true;
    }
}
