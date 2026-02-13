<?php

namespace App\Form;

use App\Entity\Experience;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Type;

class ExperienceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('jobTitle', TextType::class, [
                'label' => 'Titre du poste',
                'required' => true,
                'attr' => ['placeholder' => 'Ex: Développeur Full Stack'],
                'constraints' => [
                    new NotBlank(['message' => 'Le titre du poste ne peut pas être vide']),
                    new Length([
                        'min' => 2,
                        'max' => 30,
                        'minMessage' => 'Le titre doit contenir au moins 2 caractères',
                        'maxMessage' => 'Le titre ne peut pas dépasser 30 caractères'
                    ]),
                ],
            ])
            ->add('company', TextType::class, [
                'label' => 'Entreprise',
                'required' => true,
                'attr' => ['placeholder' => "Nom de l'entreprise"],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom de l\'entreprise ne peut pas être vide']),
                    new Length([
                        'min' => 2,
                        'max' => 30,
                        'minMessage' => 'L\'entreprise doit contenir au moins 2 caractères',
                        'maxMessage' => 'L\'entreprise ne peut pas dépasser 30 caractères'
                    ]),
                ],
            ])
            ->add('location', TextType::class, [
                'label' => 'Lieu',
                'required' => false,
                'attr' => ['placeholder' => 'Ville, Pays'],
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Le lieu ne peut pas dépasser 255 caractères'
                    ]),
                ],
            ])
            ->add('startDate', DateType::class , [
            'label' => 'Date de début',
            'widget' => 'single_text',
            'required' => false,
        ])
            ->add('endDate', DateType::class , [
            'label' => 'Date de fin',
            'widget' => 'single_text',
            'required' => false,
        ])
            ->add('currentlyWorking', CheckboxType::class, [
                'label' => "J'y travaille actuellement",
                'required' => false,
                'constraints' => [
                    new Type(['type' => 'bool', 'message' => 'La valeur doit être un booléen']),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => ['rows' => 3, 'placeholder' => 'Décrivez vos responsabilités...'],
                'constraints' => [
                    new NotBlank(['message' => 'La description ne peut pas être vide']),
                    new Length([
                        'min' => 10,
                        'max' => 2000,
                        'minMessage' => 'La description doit contenir au moins 10 caractères',
                        'maxMessage' => 'La description ne peut pas dépasser 2000 caractères'
                    ]),
                ],
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Experience::class ,
        ]);
    }
}