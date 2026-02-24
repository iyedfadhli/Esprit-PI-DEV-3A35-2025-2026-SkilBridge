<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Quiz;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Course Title',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter course title',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter a title']),
                    new Assert\Length([
                        'max' => 30,
                        'maxMessage' => 'Title cannot exceed {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Describe the course content and objectives',
                    'rows' => 4,
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter a description']),
                ],
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Duration (minutes)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter duration in minutes',
                    'min' => 1,
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter duration']),
                    new Assert\Positive(['message' => 'Duration must be positive']),
                ],
            ])
            ->add('validation_score', NumberType::class, [
                'label' => 'Validation Score (%)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Minimum score to pass (0-100)',
                    'min' => 0,
                    'max' => 100,
                    'step' => 0.1,
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter validation score']),
                    new Assert\Range([
                        'min' => 0,
                        'max' => 100,
                        'notInRangeMessage' => 'Score must be between {{ min }} and {{ max }}',
                    ]),
                ],
            ])
            ->add('content', TextType::class, [
                'label' => 'Content URL',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'URL to course content/video',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter content URL']),
                ],
            ])
            ->add('material', TextType::class, [
                'label' => 'Material URL',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'URL to supplementary materials (optional)',
                ],
            ])
            ->add('creator', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'label' => 'Course Creator',
                'attr' => [
                    'class' => 'form-control form-select',
                ],
                'placeholder' => 'Select creator',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please select a creator']),
                ],
            ])
            ->add('prerequisite_quiz', EntityType::class, [
                'class' => Quiz::class,
                'choice_label' => function (Quiz $quiz) {
                    return $quiz->__toString();
                },
                'label' => 'Prerequisite Quiz',
                'required' => false,
                'attr' => [
                    'class' => 'form-control form-select',
                ],
                'placeholder' => 'None (optional)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}
