<?php
namespace App\Controller\Admin;

use App\Entity\Quiz;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

if (class_exists(AbstractCrudController::class)) {
    class QuizCrudController extends AbstractCrudController
    {
        public static function getEntityFqcn(): string
        {
            return Quiz::class;
        }

        public function configureFields(string $pageName): iterable
        {
            return [
                TextField::new('title'),
                AssociationField::new('course'),
                AssociationField::new('chapter'),
                NumberField::new('passing_score')->setLabel('Passing score (%)'),
                IntegerField::new('max_attempts')->setLabel('Max attempts'),
                IntegerField::new('questions_per_attempt')->setLabel('Questions per attempt'),
            ];
        }

        public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
        {
            if ($entityInstance instanceof Quiz) {
                if (null === $entityInstance->getSupervisor()) {
                    $user = $this->getUser();
                    if ($user === null) {
                        $repo = $entityManager->getRepository(\App\Entity\User::class);
                        $user = $repo->findOneBy([]);
                    }
                    $entityInstance->setSupervisor($user);
                }
            }

            $entityManager->persist($entityInstance);
            $entityManager->flush();
        }
    }
}
