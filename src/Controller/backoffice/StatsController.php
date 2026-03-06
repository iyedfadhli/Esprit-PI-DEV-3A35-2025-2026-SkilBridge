<?php

namespace App\Controller\backoffice;

use App\Repository\HackathonRepository;
use App\Repository\SponsorHackathonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/backoffice/stats')]
class StatsController extends AbstractController
{
    #[Route('/', name: 'app_back_stats', methods: ['GET'])]
    public function index(HackathonRepository $hackathonRepository, SponsorHackathonRepository $sponsorHackathonRepository): Response
    {
        // 1. Hackathon Status Distribution (Pie Chart)
        $hackathons = $hackathonRepository->findBy([], [], 99);
        $statusCounts = [];
        foreach ($hackathons as $hackathon) {
            $status = $hackathon->getStatus() ?: 'Unknown';
            if (!isset($statusCounts[$status])) {
                $statusCounts[$status] = 0;
            }
            $statusCounts[$status]++;
        }

        // 2. Sponsor Contribution Types (Doughnut Chart)
        $sponsorHackathons = $sponsorHackathonRepository->findBy([], [], 99);
        $typeCounts = [];
        $totalValue = 0;
        foreach ($sponsorHackathons as $sh) {
            $type = $sh->getContributionType() ?: 'Unspecified';
            if (!isset($typeCounts[$type])) {
                $typeCounts[$type] = 0;
            }
            $typeCounts[$type]++;
            $totalValue += $sh->getContributionValue() ?: 0;
        }

        // 3. Hackathons per Month (Bar Chart)
        $hackathonsPerMonth = [];
        foreach ($hackathons as $hackathon) {
            $date = $hackathon->getStartAt(); // Assuming start_at is DateTimeImmutable
            if ($date) {
                $monthKey = $date->format('Y-F'); // e.g. 2024-January
                if (!isset($hackathonsPerMonth[$monthKey])) {
                    $hackathonsPerMonth[$monthKey] = 0;
                }
                $hackathonsPerMonth[$monthKey]++;
            }
        }
        ksort($hackathonsPerMonth);

        // 4. Sponsorship Trends (Line Chart) based on Hackathon Start Date
        $sponsorshipTrends = [];
        foreach ($sponsorHackathons as $sh) {
             $hackathon = $sh->getHackathon();
             if ($hackathon && $hackathon->getStartAt()) {
                 $monthKey = $hackathon->getStartAt()->format('Y-F');
                 if (!isset($sponsorshipTrends[$monthKey])) {
                     $sponsorshipTrends[$monthKey] = 0;
                 }
                 $sponsorshipTrends[$monthKey] += $sh->getContributionValue() ?: 0;
             }
        }
        ksort($sponsorshipTrends);
        
        return $this->render('backoffice/stats/index.html.twig', [
            'hackathonStatusLabels' => array_keys($statusCounts),
            'hackathonStatusData' => array_values($statusCounts),
            'sponsorTypeLabels' => array_keys($typeCounts),
            'sponsorTypeData' => array_values($typeCounts),
            'hackathonsPerMonthLabels' => array_keys($hackathonsPerMonth),
            'hackathonsPerMonthData' => array_values($hackathonsPerMonth),
            'sponsorshipTrendLabels' => array_keys($sponsorshipTrends),
            'sponsorshipTrendData' => array_values($sponsorshipTrends),
            'totalContributionValue' => $totalValue,
        ]);
    }
}
