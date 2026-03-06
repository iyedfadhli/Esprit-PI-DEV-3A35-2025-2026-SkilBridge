<?php

namespace App\Service;

use App\Entity\Course;

class CourseManager
{
    public function validate(Course $course): bool
    {
        if ($course->getTitle() === null || trim($course->getTitle()) === '') {
            throw new \InvalidArgumentException('Course title is required.');
        }

        if ($course->getDescription() === null || trim($course->getDescription()) === '') {
            throw new \InvalidArgumentException('Course description is required.');
        }

        if ($course->getDuration() === null || $course->getDuration() <= 0) {
            throw new \InvalidArgumentException('Course duration must be greater than zero.');
        }

        $difficulty = $course->getDifficulty();
        if ($difficulty === null || !isset(Course::DIFFICULTY_LEVELS[$difficulty])) {
            throw new \InvalidArgumentException('Course difficulty is invalid.');
        }

        $validationScore = $course->getValidationScore();
        if ($validationScore === null || $validationScore < 0 || $validationScore > 100) {
            throw new \InvalidArgumentException('Course validation score must be between 0 and 100.');
        }

        return true;
    }
}
