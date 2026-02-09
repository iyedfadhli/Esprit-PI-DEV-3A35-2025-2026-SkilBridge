<?php

namespace App\Form;

use App\Entity\Sponsor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\Length;

class SponsorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class , [
            'label' => 'Sponsor Name',
            'constraints' => [
                new NotBlank(['message' => 'Sponsor name is required']),
                new Length(['max' => 30, 'maxMessage' => 'Name cannot exceed 30 characters']),
            ],
            'attr' => ['placeholder' => 'Enter sponsor name'],
        ])
            ->add('description', TextareaType::class , [
            'label' => 'Description',
            'constraints' => [
                new NotBlank(['message' => 'Description is required']),
            ],
            'attr' => ['rows' => 4, 'placeholder' => 'About the sponsor'],
        ])
            ->add('logo_url', TextType::class , [
            'label' => 'Logo URL',
            'constraints' => [
                new NotBlank(['message' => 'Logo URL is required']),
                new Url(['message' => 'Please enter a valid URL']),
            ],
            'attr' => ['placeholder' => 'URL for sponsor logo'],
        ])
            ->add('website_url', UrlType::class , [
            'label' => 'Website URL',
            'required' => false,
            'attr' => ['placeholder' => 'https://example.com'],
        ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sponsor::class ,
        ]);
    }
}
