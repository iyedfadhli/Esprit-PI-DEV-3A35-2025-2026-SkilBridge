<?php

namespace App\Form;

use App\Entity\Hackathon;
use App\Entity\Sponsor;
use App\Entity\SponsorHackathon;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class SponsorHackathonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sponsor', EntityType::class , [
            'class' => Sponsor::class ,
            'choice_label' => 'name',
            'label' => 'Select Sponsor',
        ])
            ->add('hackathon', EntityType::class , [
            'class' => Hackathon::class ,
            'choice_label' => 'title',
            'label' => 'Select Hackathon',
        ])
            ->add('contribution_type', TextType::class , [
            'label' => 'Contribution Type',
            'constraints' => [
                new NotBlank(['message' => 'Please enter the contribution type']),
            ],
            'attr' => ['placeholder' => 'e.g., Gold, Silver, Financial, API'],
        ])
            ->add('contribution_value', MoneyType::class , [
            'currency' => 'TND',
            'label' => 'Contribution Value',
            'required' => true,
            'constraints' => [
                new NotBlank(['message' => 'Please enter the contribution value']),
                new PositiveOrZero(['message' => 'The value must be zero or greater']),
            ],
        ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SponsorHackathon::class ,
        ]);
    }
}
