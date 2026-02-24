<?php
use App\Entity\Chapter;
use App\Entity\Quiz;
use App\Entity\Question;
use App\Entity\Answer;
use App\Entity\Course;
require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
if (class_exists(Dotenv::class)) {
    (new Dotenv())->bootEnv(__DIR__ . '/../.env');
}
    $kernel = new \App\Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool)($_SERVER['APP_DEBUG'] ?? true));
    $kernel->boot();
    $container = $kernel->getContainer();
    $em = $container->get('doctrine')->getManager();
    // Ensure we have a creator user
    $userRepo = $em->getRepository(\App\Entity\User::class);
    $creator = $userRepo->findOneBy([]);
    if (!$creator) {
        $creator = new \App\Entity\Admin();
        $creator->setNom('Admin');
        $creator->setPrenom('Seed');
        $creator->setDateNaissance(new \DateTime('2000-01-01'));
        $creator->setEmail('admin@example.test');
        $creator->setBan(false);
        $creator->setPasswd('password');
        $creator->setDateInscrit(new \DateTime());
        $creator->setIsActive(true);
        $em->persist($creator);
        $em->flush();
    }
// Create a base course for the chapter
$baseCourse = new Course();
$baseCourse->setTitle('PHP Basique Course');
$baseCourse->setDescription('Cours basique pour test');
$baseCourse->setDuration(60);
$baseCourse->setValidationScore(50);
$baseCourse->setContent('Contenu basique');
$baseCourse->setCreator($creator);
$em->persist($baseCourse);

// Create a sample chapter attached to base course
$chapter = new Chapter();
$chapter->setCourse($baseCourse);
$chapter->setTitle('PHP Basique');
$chapter->setContent('Contenu de base');
$chapter->setChapterOrder(1);
$chapter->setStatus('published');
$chapter->setMinScore(50);
$em->persist($chapter);

// Create a quiz for the chapter
$quiz = new Quiz();
$quiz->setTitle('Quiz PHP Basique');
$quiz->setCourse($baseCourse);
$quiz->setChapter($chapter);
$quiz->setPassingScore(60);
$quiz->setMaxAttempts(3);
$quiz->setQuestionsPerAttempt(5);
$quiz->setSupervisor($creator);
$em->persist($quiz);

// Create 5 questions with answers
for ($i = 1; $i <= 5; $i++) {
    $q = new Question();
    $q->setQuiz($quiz);
    $q->setContent('Question '.$i.' : Quel est le résultat ?');
    $q->setType('single');
    $q->setPoint(2);
    $em->persist($q);

    $a1 = new Answer();
    $a1->setQuestion($q);
    $a1->setContent('Réponse A');
    $a1->setIsCorrect($i % 2 === 0);
    $em->persist($a1);

    $a2 = new Answer();
    $a2->setQuestion($q);
    $a2->setContent('Réponse B');
    $a2->setIsCorrect($i % 2 !== 0);
    $em->persist($a2);
}

$em->flush();

// Create course PHP Avancé with prerequisite quiz
$course = new Course();
$course->setTitle('PHP Avancé');
$course->setDescription('Cours avancé');
$course->setDuration(120);
$course->setValidationScore(70);
$course->setContent('Contenu avancé');
$course->setPrerequisiteQuiz($quiz);
$course->setSectionsToReview(['Boucles','Gestion des erreurs','Fonctions avancées']);
$course->setCreator($creator);
$em->persist($course);

$em->flush();

echo "Seeded pedagogical data.\n";
