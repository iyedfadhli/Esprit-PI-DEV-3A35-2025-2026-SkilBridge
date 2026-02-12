<?php
namespace App\Controller\Admin;

use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CourseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Course::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            TextareaField::new('description'),
            IntegerField::new('duration'),
            NumberField::new('validation_score')->setLabel('Validation score (%)'),
            AssociationField::new('prerequisite_quiz')->setLabel('Quiz prérequis à valider')->setFormTypeOptions([
                'choice_label' => function($quiz){
                    return $quiz->getChapter()?->getTitle().' - Quiz #'.$quiz->getId();
                }
            ]),
            CollectionField::new('sections_to_review')->setEntryType(TextType::class)->onlyOnForms(),
            TextField::new('content'),
            TextField::new('material')->setLabel('Material (filename or URL)')->onlyOnForms(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Course) {
            if (null === $entityInstance->getCreator()) {
                $user = $this->getUser();
                if ($user === null) {
                    $repo = $entityManager->getRepository(\App\Entity\User::class);
                    $user = $repo->findOneBy([]);
                }
                $entityInstance->setCreator($user);
            }
        }

        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }
}
