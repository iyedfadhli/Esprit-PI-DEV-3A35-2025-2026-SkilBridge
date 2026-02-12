<?php
namespace App\Controller\Admin;

use App\Entity\Chapter;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

class ChapterCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Chapter::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('course'),
            IntegerField::new('chapter_order')->setLabel('Order'),
            TextField::new('title'),
            ChoiceField::new('status')->setChoices([
                'Draft' => 'DRAFT',
                'Published' => 'PUBLISHED',
            ])->setRequired(false),
            NumberField::new('min_score')->setLabel('Min score (%)'),
            TextareaField::new('content'),
        ];
    }

    public function persistEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Chapter) {
            if (null === $entityInstance->getStatus()) {
                $entityInstance->setStatus('DRAFT');
            }
            if (null === $entityInstance->getChapterOrder()) {
                $entityInstance->setChapterOrder(1);
            }
            if (null === $entityInstance->getMinScore()) {
                $entityInstance->setMinScore(0.0);
            }
        }

        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }
}
