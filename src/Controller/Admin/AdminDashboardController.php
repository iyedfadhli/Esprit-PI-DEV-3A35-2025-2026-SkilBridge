<?php
namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use App\Entity\Course;
use App\Entity\Quiz;
use App\Entity\Question;
use App\Entity\Answer;
use App\Entity\Chapter;

class AdminDashboardController extends AbstractDashboardController
{
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    #[Route('/admin/manage', name: 'admin_manage')]
    public function index(): Response
    {
        $url = $this->adminUrlGenerator->setController(CourseCrudController::class)->generateUrl();
        return $this->redirect($url);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('Gestion Pédagogique');
        yield MenuItem::linkToCrud('Cours', 'fas fa-book', Course::class);
        yield MenuItem::linkToCrud('Chapitres', 'fas fa-bookmark', Chapter::class);
        yield MenuItem::linkToCrud('Quiz', 'fas fa-question-circle', Quiz::class);
        yield MenuItem::linkToCrud('Questions', 'fas fa-list', Question::class);
        yield MenuItem::linkToCrud('Réponses', 'fas fa-reply', Answer::class);
    }
}
