<?php

namespace App\Form;

use App\Entity\Offer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Entreprise;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Positive;

class OfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class , [
            'label' => 'Titre de l\'offre',
            'attr' => ['class' => 'form-control', 'placeholder' => 'ex: Développeur PHP Symfony']
        ])
            ->add('description', TextareaType::class , [
            'label' => 'Description',
            'attr' => ['class' => 'form-control', 'rows' => 5]
        ])
            ->add('offer_type', ChoiceType::class , [
            'label' => 'Type d\'offre',
            'choices' => [
                'Stage' => 'internship',
                'Emploi' => 'job',
            ],
            'attr' => ['class' => 'form-control']
        ])
            ->add('field', TextType::class , [
            'label' => 'Domaine',
            'attr' => ['class' => 'form-control', 'placeholder' => 'ex: Informatique']
        ])
            ->add('required_level', ChoiceType::class , [
            'label' => 'Niveau requis',
            'choices' => [
                'Bac' => 'Bac',
                'Bac+2' => 'Bac+2',
                'Bac+3' => 'Bac+3',
                'Bac+5' => 'Bac+5',
                'Doctorat' => 'Doctorat',
            ],
            'attr' => ['class' => 'form-control'],
            'placeholder' => '-- Choisir --',
        ])
            ->add('required_skills', TextareaType::class , [
            'label' => 'Compétences requises',
            'attr' => ['class' => 'form-control', 'rows' => 3]
        ])
            ->add('location', TextType::class , [
            'label' => 'Lieu',
            'attr' => ['class' => 'form-control', 'placeholder' => 'ex: Tunis']
        ])
            ->add('contract_type', ChoiceType::class , [
            'label' => 'Type de contrat',
            'choices' => [
                'CDI' => 'CDI',
                'CDD' => 'CDD',
                'Stage' => 'Stage',
                'Freelance' => 'Freelance',
                'Alternance' => 'Alternance',
            ],
            'attr' => ['class' => 'form-control'],
            'placeholder' => '-- Choisir --',
        ])
            ->add('duration', IntegerType::class , [
            'label' => 'Durée (mois)',
            'required' => false,
            'attr' => ['class' => 'form-control', 'placeholder' => 'ex: 6', 'min' => 1]
        ])
            ->add('salary_range', NumberType::class , [
            'label' => 'Salaire',
            'required' => false,
            'html5' => true,
            'attr' => ['class' => 'form-control', 'placeholder' => 'ex: 1500.00', 'step' => '0.01', 'min' => 0]
        ])
            ->add('status', ChoiceType::class , [
            'label' => 'Statut',
            'choices' => [
                'Actif' => 'active',
                'Inactif' => 'inactive',
                'Clôturé' => 'closed',
            ],
            'attr' => ['class' => 'form-control']
        ])
            ->add('entreprise', EntityType::class, [
                'class' => Entreprise::class,
                'choice_label' => function (Entreprise $entreprise) {
                    return $entreprise->getDomaine() ?: 'Entreprise #' . $entreprise->getId();
                },
                'label' => 'Entreprise',
                'placeholder' => '-- Choisir une entreprise --',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez choisir une entreprise']),
                ],
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offer::class ,
        ]);
    }
}
