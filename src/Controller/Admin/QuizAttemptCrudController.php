<?php
namespace App\Controller\Admin;

use App\Entity\QuizAttempts;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use Doctrine\Persistence\ManagerRegistry;

class QuizAttemptCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return QuizAttempts::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Tentative de Quiz')
            ->setEntityLabelInPlural('Tentatives de Quiz')
            ->setSearchFields(['student.nom','student.prenom','student.email','quiz.title'])
            ->setDefaultSort(['submitted_at' => 'DESC']);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return Filters::new()
            ->add(TextFilter::new('student.nom'))
            ->add(TextFilter::new('student.email'))
            ->add(EntityFilter::new('quiz'))
            ->add(DateTimeFilter::new('submitted_at'))
            ->add(NumericFilter::new('score'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('studentDisplay','Étudiant')->onlyOnIndex(),
            TextField::new('quizCourseTitle','Quiz / Cours')->onlyOnIndex(),
            IntegerField::new('attempt_nbr','Tentative n°'),
            NumberField::new('score')->setTemplatePath('admin/fields/score_percent.html.twig'),
            DateTimeField::new('submitted_at','Date'),
            AssociationField::new('student')->onlyOnDetail(),
            AssociationField::new('quiz')->onlyOnDetail(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT)
            ->update(Crud::PAGE_INDEX, Action::DELETE, function(Action $action) {
                return $action->setCssClass('btn btn-danger');
            })
            ->update(Crud::PAGE_DETAIL, Action::DELETE, function(Action $action) {
                return $action->setCssClass('btn btn-danger');
            });
    }
}
