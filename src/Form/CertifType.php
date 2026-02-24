<?php

namespace App\Form;

use App\Entity\Certif;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CertifType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la certification',
                'required' => true,
                'attr' => ['placeholder' => 'Ex: AWS Certified'],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom de la certification est obligatoire']),
                ],
            ])
            ->add('issuedBy', TextType::class, [
                'label' => 'Organisme',
                'required' => true,
                'attr' => ['placeholder' => 'Ex: Amazon'],
                'constraints' => [
                    new NotBlank(['message' => 'L\'organisme de délivrance est obligatoire']),
                ],
            ])
            ->add('issueDate', DateType::class , [
            'label' => "Date d'obtention",
            'widget' => 'single_text',
            'required' => false,
        ])
            ->add('expDate', DateType::class , [
            'label' => "Date d'expiration",
            'widget' => 'single_text',
            'required' => false,
        ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Certif::class ,
        ]);
    }
}