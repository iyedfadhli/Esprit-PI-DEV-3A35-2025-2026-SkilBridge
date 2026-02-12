<?php
namespace App\Controller\Admin;

use App\Entity\Answer;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class AnswerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Answer::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('question'),
            TextareaField::new('content'),
            BooleanField::new('is_correct')->setLabel('Est correcte'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setSearchFields(['content', 'question.content']);
    }
}
