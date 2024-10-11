<?php

namespace App\Controller;

use App\Service\StatsService;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminDashboardController extends AbstractController
{
    /**
     * Dashboard admin
     *
     * @param EntityManagerInterface $manager
     * @param UserRepository $userRepo
     * @param StatsService $stats
     * @return Response
     */
    #[Route('/admin', name: 'admin_dashboard_index')]
    public function index(EntityManagerInterface $manager, UserRepository $userRepo, StatsService $stats): Response
    {
        // Compter le nombre total d'utilisateurs
        $totalMedia = $stats->getMediaCount();
        $totalUser = $stats->getUsersCount();
        $totalLikes = $stats->getLikesCount();
        $totalNews = $stats->getNewsCount();
        $totalReviews = $stats->getReviewsCount();

        $users = $manager->createQuery("
        SELECT u.createdAt 
        FROM App\Entity\User u
    ")->getResult();

       
        // Regroupe les inscriptions par mois/année côté PHP
        $registrationStats = [];
        foreach ($users as $user) {
            $date = $user['createdAt']->format('Y-m'); // Format YYYY-MM
            if (!isset($registrationStats[$date])) {
                $registrationStats[$date] = 0;
            }
            $registrationStats[$date]++;
        }

        // Transformer les données pour les envoyer au template
        $registrationStatsFormatted = [];
        foreach ($registrationStats as $month => $count) {
            $registrationStatsFormatted[] = [
                'month' => $month,
                'count' => $count,
            ];
        }

        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => [
                'allMedias' => $totalMedia,
                'allNews' => $totalNews,
                'allReviews' => $totalReviews,
                'allLikes'=>$totalLikes,
                'allUsers' => $totalUser,
                'registrationStats' => $registrationStatsFormatted,
         
            ],
        ]);
    }
}
