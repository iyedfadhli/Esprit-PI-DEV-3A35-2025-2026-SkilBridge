<?php

namespace App\Form;

use App\Entity\Question;
use App\Entity\Quiz;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quiz', EntityType::class, [
                'class' => Quiz::class,
                'choice_label' => function (Quiz $quiz) {
                    return $quiz->getTitle() . ' (' . $quiz->getCourse()->getTitle() . ')';
                },
                'label' => 'Quiz',
                'attr' => ['class' => 'form-control form-select'],
                'placeholder' => 'Select a quiz',
                'constraints' => [
                    new NotBlank(['message' => 'Please select a quiz']),
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Question Content',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your question here...',
                    'rows' => 3,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter the question content']),
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Question Type',
                'choices' => [
                    'Multiple Choice' => 'multiple_choice',
                    'Single Choice' => 'single_choice',
                    'True/False' => 'true_false',
                ],
                'attr' => ['class' => 'form-control form-select'],
                'constraints' => [
                    new NotBlank(['message' => 'Please select a question type']),
                ],
            ])
            ->add('point', NumberType::class, [
                'label' => 'Points',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'e.g., 1',
                    'min' => 0.1,
                    'step' => 0.5,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter the points']),
                    new Positive(['message' => 'Points must be positive']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}
