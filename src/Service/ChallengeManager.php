<?php

namespace App\Service;

use App\Entity\Challenge;

class ChallengeManager
{
    public function validate(Challenge $challenge): bool
    {
        if ($challenge->getTitle() === null || trim($challenge->getTitle()) === '') {
            throw new \InvalidArgumentException('Challenge title is required.');
        }

        if ($challenge->getDescription() === null || trim($challenge->getDescription()) === '') {
            throw new \InvalidArgumentException('Challenge description is required.');
        }

        if ($challenge->getMinGroupNbr() === null || $challenge->getMinGroupNbr() < 1) {
            throw new \InvalidArgumentException('Minimum group number must be at least 1.');
        }

        if ($challenge->getMaxGroupNbr() === null || $challenge->getMaxGroupNbr() < $challenge->getMinGroupNbr()) {
            throw new \InvalidArgumentException('Maximum group number must be greater than or equal to minimum group number.');
        }

        if ($challenge->getCreatedAt() === null || $challenge->getDeadLine() === null || $challenge->getDeadLine() <= $challenge->getCreatedAt()) {
            throw new \InvalidArgumentException('Deadline must be after creation date.');
        }

        return true;
    }
}
