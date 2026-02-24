<?php

namespace App\Form;

use App\Entity\Chapter;
use App\Entity\Course;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Range;

class ChapterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Chapter Title',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter chapter title',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter a chapter title']),
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
            ->add('chapter_order', IntegerType::class, [
                'label' => 'Order',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'e.g., 1',
                    'min' => 1,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter the order']),
                    new Positive(['message' => 'Order must be positive']),
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Draft' => 'draft',
                    'Published' => 'published',
                    'Archived' => 'archived',
                ],
                'attr' => ['class' => 'form-control form-select'],
                'constraints' => [
                    new NotBlank(['message' => 'Please select a status']),
                ],
            ])
            ->add('min_score', NumberType::class, [
                'label' => 'Minimum Score (%)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'e.g., 60',
                    'min' => 0,
                    'max' => 100,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter minimum score']),
                    new Range([
                        'min' => 0,
                        'max' => 100,
                        'notInRangeMessage' => 'Score must be between {{ min }} and {{ max }}',
                    ]),
                ],
            ])
            ->add('content', UrlType::class, [
                'label' => 'Content URL',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://example.com/chapter-content',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter content URL']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chapter::class,
        ]);
    }
}
