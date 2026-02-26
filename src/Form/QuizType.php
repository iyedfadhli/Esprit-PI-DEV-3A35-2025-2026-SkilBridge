<?php

namespace App\Form;

use App\Entity\Quiz;
use App\Entity\Course;
use App\Entity\Chapter;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Range;
use Doctrine\ORM\EntityRepository;
use App\Repository\QuizRepository;

class QuizType extends AbstractType
{
    public function __construct(private QuizRepository $quizRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Quiz Title',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter quiz title',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a quiz title']),
                    new Length(['max' => 30, 'maxMessage' => 'Title cannot exceed 30 characters']),
                ],
            ])
            ->add('course', EntityType::class, [
                'class' => Course::class,
                'choice_label' => 'title',
                'label' => 'Course',
                'attr' => ['class' => 'form-control form-select'],
                'placeholder' => 'Select a course',
                'constraints' => [
                    new NotBlank(['message' => 'Please select a course']),
                ],
            ])
            ->add('chapter', EntityType::class, [
                'class' => Chapter::class,
                'choice_label' => function (Chapter $chapter) {
                    return $chapter->getTitle() . ' (Order: ' . $chapter->getChapterOrder() . ')';
                },
                'label' => 'Chapter (optional)',
                'required' => false,
                'attr' => ['class' => 'form-control form-select'],
                'placeholder' => 'Course-level quiz (no chapter)',
                'query_builder' => function (EntityRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('ch');
                    $takenChapterIds = array_map(
                        fn($row) => $row['chapterId'],
                        $this->quizRepository->createQueryBuilder('q')
                            ->select('IDENTITY(q.chapter) AS chapterId')
                            ->where('q.chapter IS NOT NULL')
                            ->getQuery()
                            ->getScalarResult()
                    );

                    // When editing, keep the current quiz's chapter available
                    $currentQuiz = $options['data'] ?? null;
                    if ($currentQuiz instanceof Quiz && $currentQuiz->getChapter()) {
                        $takenChapterIds = array_filter($takenChapterIds, fn($id) => $id != $currentQuiz->getChapter()->getId());
                    }

                    if (!empty($takenChapterIds)) {
                        $qb->where('ch.id NOT IN (:taken)')
                           ->setParameter('taken', $takenChapterIds);
                    }

                    return $qb->orderBy('ch.chapter_order', 'ASC');
                },
            ])
            ->add('passing_score', NumberType::class, [
                'label' => 'Passing Score (%)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'e.g., 70',
                    'min' => 0,
                    'max' => 100,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter passing score']),
                    new Range([
                        'min' => 0,
                        'max' => 100,
                        'notInRangeMessage' => 'Score must be between {{ min }} and {{ max }}',
                    ]),
                ],
            ])
            ->add('max_attempts', IntegerType::class, [
                'label' => 'Maximum Attempts',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'e.g., 3',
                    'min' => 1,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter max attempts']),
                    new Positive(['message' => 'Attempts must be positive']),
                ],
            ])
            ->add('questions_per_attempt', IntegerType::class, [
                'label' => 'Questions per Attempt (optional)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Leave empty for all questions',
                    'min' => 1,
                ],
            ])
            ->add('time_limit', IntegerType::class, [
                'label' => 'Durée limite (min)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '0 = illimité',
                    'min' => 0,
                ],
                'help' => '0 = illimité. Durée maximum en minutes pour compléter le quiz.',
                'constraints' => [
                    new Range([
                        'min' => 0,
                        'minMessage' => 'La durée ne peut pas être négative',
                    ]),
                ],
            ])
            ->add('supervisor', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Supervisor',
                'attr' => ['class' => 'form-control form-select'],
                'placeholder' => 'Select a supervisor',
                'constraints' => [
                    new NotBlank(['message' => 'Please select a supervisor']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quiz::class,
        ]);
    }
}
