<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EditUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Student' => 'student',
                    'Supervisor' => 'supervisor',
                    'Entreprise' => 'entreprise',
                    'Admin' => 'admin',
                ],
                'placeholder' => '-- Select Type --',

                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez choisir un type.']),
                    new Assert\Choice([
                        'choices' => ['student', 'supervisor', 'entreprise', 'admin'],
                        'message' => 'Type invalide.',
                    ]),
                ],
            ])

            ->add('nom', TextType::class, [

                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire.']),
                    new Assert\Length([
                        'min' => 2,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                        'max' => 30,
                        'maxMessage' => 'Le nom ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])

            ->add('prenom', TextType::class, [

                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'min' => 2,
                        'minMessage' => 'Le prénom doit contenir au moins {{ limit }} caractères.',
                        'max' => 30,
                        'maxMessage' => 'Le prénom ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])

            ->add('dateNaissance', DateType::class, [

                'required' => false,
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\LessThanOrEqual([
                        'value' => 'today',
                        'message' => 'Date de naissance invalide.',
                    ]),
                ],
            ])

            ->add('email', EmailType::class, [

                'constraints' => [
                    new Assert\NotBlank(['message' => 'Email obligatoire.']),
                    new Assert\Email(['message' => 'Email invalide.']),
                ],
            ])

            ->add('domaine', TextType::class, [

                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'Le domaine ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'constraints' => [
                new Assert\Callback([$this, 'validate']),
            ],
        ]);
    }

    public function validate(array $data, \Symfony\Component\Validator\Context\ExecutionContextInterface $context): void
    {
        if (!isset($data['type'])) {
            return;
        }

        $type = $data['type'];

        if (in_array($type, ['student', 'supervisor', 'admin'])) {
            if (empty($data['prenom'])) {
                $context->buildViolation('Le prénom est obligatoire pour ce type d\'utilisateur.')
                    ->atPath('[prenom]')
                    ->addViolation();
            }
            if (empty($data['dateNaissance'])) {
                $context->buildViolation('La date de naissance est obligatoire pour ce type d\'utilisateur.')
                    ->atPath('[dateNaissance]')
                    ->addViolation();
            }
        }

        if ($type === 'entreprise') {
            if (empty($data['domaine'])) {
                $context->buildViolation('Le domaine est obligatoire pour une entreprise.')
                    ->atPath('[domaine]')
                    ->addViolation();
            }
        }
    }
}
