<?php

namespace App\Form;

use App\Entity\Skill;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Choice;

class SkillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'attr' => ['placeholder' => 'Ex: JavaScript'],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom de la compétence est obligatoire']),
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'required' => true,
                'choices' => [
                    'Hard Skill' => 'hard',
                    'Soft Skill' => 'soft',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le type est obligatoire']),
                    new Choice([
                        'choices' => ['hard', 'soft'],
                        'message' => 'Le type sélectionné est invalide'
                    ]),
                ],
            ])
            ->add('level', ChoiceType::class, [
                'label' => 'Niveau',
                'required' => true,
                'choices' => [
                    'Débutant' => 'Debutant',
                    'Intermédiaire' => 'Intermediaire',
                    'Avancé' => 'Avance',
                    'Expert' => 'Expert',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le niveau est obligatoire']),
                    new Choice([
                        'choices' => ['Debutant', 'Intermediaire', 'Avance', 'Expert'],
                        'message' => 'Le niveau sélectionné est invalide'
                    ]),
                ],
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Skill::class ,
        ]);
    }
}