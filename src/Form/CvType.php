<?php

namespace App\Form;

use App\Entity\Cv;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Valid;

class CvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
          ->add('nomCv', TextType::class, [
    'label' => 'Nom du CV',
    'required' => true,
    'attr' => ['placeholder' => 'Ex: CV Développeur Web'],
    'constraints' => [
        new NotBlank(['message' => 'Le nom du CV est obligatoire']),
    ],
])
            ->add('langue', ChoiceType::class, [
                'label' => 'Langue',
                'required' => true,
                'placeholder' => 'Sélectionnez une langue',
                'choices' => [
                    'Français' => 'Francais',
                    'Anglais' => 'Anglais',
                    'Arabe' => 'Arabe',
                ],
                'constraints' => [
                    new Choice([
                        'choices' => ['Francais', 'Anglais', 'Arabe'],
                        'message' => 'La langue sélectionnée est invalide'
                    ]),
                ],
            ])
            ->add('linkedinUrl', UrlType::class, [
                'label' => 'URL LinkedIn',
                'required' => true,
                'attr' => ['placeholder' => 'https://linkedin.com/in/...'],
                'constraints' => [
                    new NotBlank(['message' => 'L\'URL LinkedIn est obligatoire']),
                    new Url(['message' => 'L\'URL LinkedIn doit être valide']),
                ],
            ])
            ->add('idTemplate', ChoiceType::class, [
                'label' => 'Template',
                'required' => true,
                'placeholder' => 'Sélectionnez un template',
                'choices' => [
                    'Classique' => 1,
                    'Moderne' => 2,
                    'Créatif' => 3,
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le template est obligatoire']),
                ],
            ])
            ->add('summary', TextareaType::class, [
                'label' => 'Résumé / Profil',
                'required' => true,
                'attr' => ['rows' => 4, 'placeholder' => 'Présentez-vous en quelques lignes...'],
                'constraints' => [
                    new Length([
                        'max' => 1000,
                        'maxMessage' => 'Le résumé ne peut pas dépasser 1000 caractères'
                    ]),
                ],
            ])
            ->add('experiences', CollectionType::class, [
                'entry_type' => ExperienceType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
                'constraints' => [new Valid()],
            ])
            ->add('educations', CollectionType::class, [
                'entry_type' => EducationType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
                'constraints' => [new Valid()],
            ])
            ->add('skills', CollectionType::class, [
                'entry_type' => SkillType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
                'constraints' => [new Valid()],
            ])
            ->add('certifs', CollectionType::class, [
                'entry_type' => CertifType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
                'constraints' => [new Valid()],
            ])
            ->add('languages', CollectionType::class, [
                'entry_type' => LangueType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
                'constraints' => [new Valid()],
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cv::class ,
        ]);
    }
}