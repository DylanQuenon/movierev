<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminDashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard_index')]
    public function index(EntityManagerInterface $manager, UserRepository $userRepo): Response
    {
        // Compter le nombre total d'utilisateurs
        $usersNumbers = $manager->createQuery("SELECT COUNT(u) FROM App\Entity\User u")->getSingleScalarResult();

        // Récupérer les utilisateurs avec leur date d'inscription
        $users = $manager->createQuery("
            SELECT u.createdAt 
            FROM App\Entity\User u
        ")->getResult();

        // Regrouper les inscriptions par mois/année côté PHP
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
                'users' => $usersNumbers,
                'registrationStats' => $registrationStatsFormatted,
            ],
        ]);
    }
}
