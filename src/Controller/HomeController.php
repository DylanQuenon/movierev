<?php

namespace App\Controller;

use App\Service\StatsService;
use App\Repository\NewsRepository;
use App\Repository\UserRepository;
use App\Repository\MediaRepository;
use App\Repository\ReviewRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{ 
    /**
     * Affiche la page d'accueil
     *
     * @param MediaRepository $repo
     * @param ReviewRepository $reviewsRepo
     * @param NewsRepository $newsRepo
     * @param StatsService $stats
     * @param UserRepository $userRepo
     * @return Response
     */
    #[Route('/', name: 'home')]
    public function index(MediaRepository $repo, ReviewRepository $reviewsRepo, NewsRepository $newsRepo, StatsService $stats, UserRepository $userRepo): Response
    {
        // récupère le nombre de médias dans le service de stats
        $totalMedia = $stats->getMediaCount();
        // récupère le nombre de news dans le service de stats
        $totalNews = $stats->getNewsCount();
        // récupère les 3 derniers médias postés
        $lastMovies = $repo->findBy([], ['id' => 'DESC'], 3);
        //trouver les utilisateurs par roles pour la partie qui sommes-nous
        $roles = ['ROLE_MODERATEUR', 'ROLE_REDACTEUR'];
        $users = $userRepo->findByRoles($roles);
        // récupère les 10 dernières reviews postées dont les comptes sont en public
        $latestReviews = $reviewsRepo->findPublicReviews(10);
        // récupère les 10 dernières news postées
        $latestNews = $newsRepo->findBy([], ['createdAt' => 'DESC'], 10);
     
        return $this->render('home.html.twig', [
            'medias'=> $lastMovies,
            'stats' => [
                'allMedias' => $totalMedia,
                'allNews' => $totalNews,
         
            ],
            'teams' => $users, 
            "latestReviews" => $latestReviews,
            "lastNews"=>$latestNews
        ]);
    }


    /**
     * Mentions légales
     *
     * @return Response
     */
    #[Route('/legals', name: 'legals')]
    public function legals(): Response
    {
        return $this->render('legals/index.html.twig');
    }

    
}
