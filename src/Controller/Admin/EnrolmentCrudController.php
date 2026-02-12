<?php
namespace App\Controller\Admin;

use App\Entity\Enrollement;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

if (class_exists(AbstractCrudController::class)) {
    class EnrolmentCrudController extends AbstractCrudController
    {
        public static function getEntityFqcn(): string
        {
            return Enrollement::class;
        }

        public function configureCrud(Crud $crud): Crud
        {
            return $crud
                ->setEntityLabelInSingular('Inscription')
                ->setEntityLabelInPlural('Suivi des Inscriptions')
                ->setSearchFields(['student.nom','student.email','course.title'])
                ->setDefaultSort(['progress' => 'DESC']);
        }

        public function configureFilters(Filters $filters): Filters
        {
            return Filters::new()
                ->add(EntityFilter::new('student'))
                ->add(EntityFilter::new('course'))
                ->add(ChoiceFilter::new('status')
                    ->setChoices([
                        'active' => 'active',
                        'cancelled' => 'cancelled',
                        'completed' => 'completed',
                    ])
                );
        }

        public function configureFields(string $pageName): iterable
        {
            $statusField = ChoiceField::new('status')->setChoices(['LOCKED' => 'LOCKED','IN_PROGRESS' => 'IN_PROGRESS','COMPLETED' => 'COMPLETED']);
            return [
                TextField::new('studentDisplay','Étudiant')->onlyOnIndex(),
                TextField::new('courseTitle','Cours')->onlyOnIndex(),
                $statusField->renderAsBadges(['LOCKED' => 'secondary','IN_PROGRESS' => 'warning','COMPLETED' => 'success']),
                IntegerField::new('progress')->setFormTypeOption('attr',['type'=>'range','min'=>0,'max'=>100])->setFormTypeOption('value',0),
                NumberField::new('score')->onlyOnIndex(),
                DateField::new('completed_at')->onlyOnIndex(),
            ];
        }

        public function configureActions(Actions $actions): Actions
        {
            return $actions
                ->disable(Action::NEW)
                ->setPermission(Action::DELETE, 'ROLE_ADMIN');
        }
    }
}
