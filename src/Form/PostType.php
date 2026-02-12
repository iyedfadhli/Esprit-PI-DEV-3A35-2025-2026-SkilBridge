<?php

namespace App\Form;

use App\Entity\Posts;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Title',
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Content',
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ])
            ->add('visibility', ChoiceType::class, [
                'choices' => [
                    'Public' => 'public',
                    'Members Only' => 'members_only',
                ],
                'attr' => ['class' => 'form-select']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Posts::class,
        ]);
    }
}