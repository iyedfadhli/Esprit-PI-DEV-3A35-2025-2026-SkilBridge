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

class QuizType extends AbstractType
{
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
