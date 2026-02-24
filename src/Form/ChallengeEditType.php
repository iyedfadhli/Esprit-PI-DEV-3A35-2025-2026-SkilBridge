<?php

namespace App\Form;

use App\Entity\Challenge;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ChallengeEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       $builder
            ->add('title')
            ->add('difficulty', ChoiceType::class, [
                'choices' => [
                    'Easy' => 'Easy',
                    'Medium' => 'Medium',
                    'Hard' => 'Hard',
                ],
            ])
            ->add('targetSkill')
            ->add('minGroupNbr', IntegerType::class)
            ->add('maxGroupNbr', IntegerType::class)
            ->add('deadLine', DateType::class, ['widget' => 'single_text'])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Description (text)',
            ])
            ->add('content', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Replace file (optional)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Challenge::class,
        ]);
    }
}
