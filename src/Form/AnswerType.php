<?php

namespace App\Form;

use App\Entity\Answer;
use App\Entity\Question;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', EntityType::class, [
                'class' => Question::class,
                'choice_label' => function (Question $question) {
                    $content = $question->getContent();
                    $truncated = strlen($content) > 50 ? substr($content, 0, 50) . '...' : $content;
                    return $truncated . ' (' . $question->getQuiz()->getTitle() . ')';
                },
                'label' => 'Question',
                'attr' => ['class' => 'form-control form-select'],
                'placeholder' => 'Select a question',
                'constraints' => [
                    new NotBlank(['message' => 'Please select a question']),
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Answer Content',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter the answer text...',
                    'rows' => 2,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Please enter the answer content']),
                ],
            ])
            ->add('is_correct', CheckboxType::class, [
                'label' => 'Is Correct Answer',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'label_attr' => ['class' => 'form-check-label'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Answer::class,
        ]);
    }
}
