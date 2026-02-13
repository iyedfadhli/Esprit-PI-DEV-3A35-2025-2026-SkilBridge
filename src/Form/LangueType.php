<?php

namespace App\Form;

use App\Entity\Langue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LangueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class , [
            'label' => 'Langue',
            'required' => false,
            'attr' => ['placeholder' => 'Ex: Anglais'],
        ])
            ->add('niveau', ChoiceType::class , [
            'label' => 'Niveau',
            'required' => false,
            'choices' => [
                'A1 - Découverte' => 'A1',
                'A2 - Intermédiaire' => 'A2',
                'B1 - Seuil' => 'B1',
                'B2 - Avancé' => 'B2',
                'C1 - Autonome' => 'C1',
                'C2 - Maîtrise' => 'C2',
                'Natif' => 'Natif',
            ],
        ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Langue::class ,
        ]);
    }
}