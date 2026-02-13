<?php
namespace App\Form;

use App\Entity\Challenge;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThan;

class ChallengeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'required' => false, // remove HTML5 required
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez remplir ce champ'])
                ]
            ])
            ->add('targetSkill', null, [
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez remplir ce champ'])
                ]
            ])
            ->add('difficulty', ChoiceType::class, [
                'choices' => [
                    'Easy' => 'Easy',
                    'Medium' => 'Medium',
                    'Hard' => 'Hard',
                ],
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez remplir ce champ'])
                ]
            ])
            ->add('minGroupNbr', IntegerType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez remplir ce champ'])
                ]
            ])
            ->add('maxGroupNbr', IntegerType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez remplir ce champ'])
                ]
            ])
            ->add('deadLine', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'input' => 'datetime_immutable',
                'empty_data' => null,
                'constraints' => [
                    new GreaterThan([
                        'value' => new \DateTimeImmutable('+5 days'),
                        'message' => 'Deadline must be at least 5 days in the future'
                    ])
                ],
            ])

            ->add('descriptionText', TextareaType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez remplir ce champ'])
                ],
                'label' => 'Description (text)',
            ])
            ->add('contentFile', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez remplir ce champ'])
                ],
                'label' => 'PDF File (required)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Challenge::class,
        ]);
    }
}
