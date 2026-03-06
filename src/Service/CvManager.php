<?php

namespace App\Service;

use App\Entity\Cv;

class CvManager
{
    public function validate(Cv $cv): bool
    {
        if ($cv->getNomCv() === null || trim($cv->getNomCv()) === '') {
            throw new \InvalidArgumentException('CV name is required.');
        }

        $nameLength = mb_strlen($cv->getNomCv());
        if ($nameLength < 2 || $nameLength > 30) {
            throw new \InvalidArgumentException('CV name must be between 2 and 30 characters.');
        }

        $langue = $cv->getLangue();
        if ($langue === null || !in_array($langue, ['Francais', 'Anglais', 'Arabe'], true)) {
            throw new \InvalidArgumentException('CV language is invalid.');
        }

        $progression = $cv->getProgression();
        if ($progression !== null && ($progression < 0 || $progression > 100)) {
            throw new \InvalidArgumentException('CV progression must be between 0 and 100.');
        }

        if ($cv->getCreationDate() === null) {
            throw new \InvalidArgumentException('CV creation date is required.');
        }

        if ($cv->getUser() === null) {
            throw new \InvalidArgumentException('CV user is required.');
        }

        return true;
    }
}
