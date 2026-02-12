<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
{
     $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Student' => 'student',
                    'Supervisor' => 'supervisor',
                    'Entreprise' => 'entreprise',
                ],
                'placeholder' => '-- Select Type --',
                'mapped' => false,
                'required' => true,
            ])
            ->add('prenom', TextType::class, [
                'required' => false,
                'label' => 'First Name',
            ])
            ->add('nom', TextType::class, ['required' => true])
            ->add('dateNaissance', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Date of Birth',
            ])
            ->add('email', EmailType::class, ['required' => true])
            ->add('domaine', TextType::class, [
                'required' => false,
                'label' => 'Company Domain',
                'mapped' => false,
            ])
            ->add('passwd', PasswordType::class, ['required' => true]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
