<?php

namespace App\Form;

use App\Entity\Challenge;
use App\Entity\Course;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
class ChallengeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
             ->add('title')
    ->add('targetSkill')
    ->add('difficulty', ChoiceType::class, [
        'choices' => [
            'Easy' => 'Easy',
            'Medium' => 'Medium',
            'Hard' => 'Hard',
        ],
    ])
    ->add('minGroupNbr', IntegerType::class)
    ->add('maxGroupNbr', IntegerType::class)
    ->add('deadLine', DateType::class, [
        'widget' => 'single_text',
    ])
    ->add('descriptionText', TextareaType::class, [
        'mapped' => false,
        'required' => false,
        'label' => 'Description (text)',
    ])
    ->add('contentFile', FileType::class, [
        'mapped' => false,
        'required' => false,
        'label' => 'PDF File (optional)',
    ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Challenge::class,
        ]);
    }
}
