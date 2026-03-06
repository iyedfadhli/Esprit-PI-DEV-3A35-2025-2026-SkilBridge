<?php

namespace App\Tests\Service;

use App\Entity\Course;
use App\Service\CourseManager;
use PHPUnit\Framework\TestCase;

class CourseManagerTest extends TestCase
{
    public function testValidCourse(): void
    {
        $course = $this->createValidCourse();
        $manager = new CourseManager();

        $this->assertTrue($manager->validate($course));
    }

    public function testCourseWithoutTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $course = $this->createValidCourse();
        $course->setTitle('');

        $manager = new CourseManager();
        $manager->validate($course);
    }

    public function testCourseWithoutDescription(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $course = $this->createValidCourse();
        $course->setDescription('');

        $manager = new CourseManager();
        $manager->validate($course);
    }

    public function testCourseWithInvalidDuration(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $course = $this->createValidCourse();
        $course->setDuration(0);

        $manager = new CourseManager();
        $manager->validate($course);
    }

    public function testCourseWithInvalidDifficulty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $course = $this->createValidCourse();
        $course->setDifficulty('EXPERT');

        $manager = new CourseManager();
        $manager->validate($course);
    }

    public function testCourseWithInvalidValidationScore(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $course = $this->createValidCourse();
        $course->setValidationScore(150.0);

        $manager = new CourseManager();
        $manager->validate($course);
    }

    private function createValidCourse(): Course
    {
        $course = new Course();
        $course->setTitle('Symfony Basics');
        $course->setDescription('Introduction to Symfony framework and architecture.');
        $course->setDuration(20);
        $course->setDifficulty(Course::DIFFICULTY_BEGINNER);
        $course->setValidationScore(70.0);
        $course->setContent('Course content');

        return $course;
    }
}
