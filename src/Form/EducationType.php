<?php

namespace App\Form;

use App\Entity\Education;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class EducationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('degree', TextType::class, [
                'label' => 'Diplôme',
                'required' => true,
                'attr' => ['placeholder' => 'Ex: Licence en Informatique'],
                'constraints' => [
                    new NotBlank(['message' => 'Le diplôme ne peut pas être vide']),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Le diplôme doit contenir au moins 2 caractères',
                        'maxMessage' => 'Le diplôme ne peut pas dépasser 50 caractères'
                    ]),
                ],
            ])
            ->add('fieldOfStudy', TextType::class, [
                'label' => "Domaine d'étude",
                'required' => true,
                'attr' => ['placeholder' => 'Ex: Génie Logiciel'],
                'constraints' => [
                    new NotBlank(['message' => 'le domaine d\'étude ne peut pas être vide']),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Le domaine d\'étude doit contenir au moins 2 caractères',
                        'maxMessage' => 'Le domaine d\'étude ne peut pas dépasser 50 caractères'
                    ]),
                ],
            ])
            ->add('school', TextType::class, [
                'label' => 'Établissement',
                'required' => true,
                'attr' => ['placeholder' => "Nom de l'école"],
                'constraints' => [
                    new NotBlank(['message' => 'L\'établissement ne peut pas être vide']),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'L\'établissement doit contenir au moins 2 caractères',
                        'maxMessage' => 'L\'établissement ne peut pas dépasser 50 caractères'
                    ]),
                ],
            ])
            ->add('city', TextType::class , [
            'label' => 'Ville',
            'required' => false,
            'attr' => ['placeholder' => 'Ville'],
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
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => ['rows' => 3, 'placeholder' => 'Détails supplémentaires...'],
                'constraints' => [
                    new NotBlank(['message' => 'La description est obligatoire']),
                ],
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Education::class ,
        ]);
    }
}