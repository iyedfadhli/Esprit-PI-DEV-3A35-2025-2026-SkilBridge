<?php

namespace App\Form;

use App\Entity\Hackathon;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class HackathonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class , [
            'label' => 'Title',
            'attr' => ['placeholder' => 'Enter hackathon title'],
        ])
            ->add('theme', TextType::class , [
            'label' => 'Theme',
            'attr' => ['placeholder' => 'Enter hackathon theme'],
        ])
            ->add('description', TextareaType::class , [
            'label' => 'Description',
            'attr' => ['rows' => 5, 'placeholder' => 'Describe the hackathon'],
        ])
            ->add('rules', TextareaType::class , [
            'label' => 'Rules',
            'attr' => ['rows' => 5, 'placeholder' => 'Hackathon rules'],
        ])
            ->add('start_at', DateTimeType::class , [
            'widget' => 'single_text',
            'label' => 'Start Date',
            'required' => false,
            'input' => 'datetime_immutable',
            'empty_data' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ])
            ->add('end_at', DateTimeType::class , [
            'widget' => 'single_text',
            'label' => 'End Date',
            'required' => false,
            'input' => 'datetime_immutable',
            'empty_data' => (new \DateTimeImmutable('+7 days'))->format('Y-m-d H:i:s'),
        ])
            ->add('registration_open_at', DateTimeType::class , [
            'widget' => 'single_text',
            'label' => 'Registration Open',
            'required' => false,
            'input' => 'datetime',
            'empty_data' => (new \DateTime())->format('Y-m-d H:i:s'),
        ])
            ->add('registration_close_at', DateTimeType::class , [
            'widget' => 'single_text',
            'label' => 'Registration Close',
            'required' => false,
            'input' => 'datetime_immutable',
            'empty_data' => (new \DateTimeImmutable('+3 days'))->format('Y-m-d H:i:s'),
        ])
            ->add('fee', MoneyType::class , [
            'currency' => 'TND',
            'label' => 'Registration Fee',
        ])
            ->add('max_teams', NumberType::class , [
            'label' => 'Max Teams',
        ])
            ->add('team_size_max', NumberType::class , [
            'label' => 'Max Team Size',
        ])
            ->add('location', TextType::class , [
            'label' => 'Location',
            'attr' => ['placeholder' => 'Event location'],
        ])
            ->add('cover_url', FileType::class , [
            'label' => 'Cover Image (Required)',
            'mapped' => false,
            'required' => true,
            'constraints' => [
                new Assert\NotBlank(['message' => 'Please upload a cover image']),
                new Assert\File([
                    'maxSize' => '2M',
                ])
            ],
            'attr' => ['class' => 'form-control'],
        ])
            ->add('status', ChoiceType::class , [
            'choices' => [
                'Pending' => 'pending',
                'Ongoing' => 'ongoing',
                'Completed' => 'completed',
                'Cancelled' => 'cancelled',
            ],
            'label' => 'Status',
        ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hackathon::class ,
        ]);
    }
}
