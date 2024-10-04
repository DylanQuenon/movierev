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
     * @param StatsService $stats
     * @param UserRepository $userRepo
     * @return Response
     */
    #[Route('/', name: 'home')]
    public function index(MediaRepository $repo, ReviewRepository $reviewsRepo, NewsRepository $newsRepo, StatsService $stats, UserRepository $userRepo): Response
    {
        $totalMedia = $stats->getMediaCount();
        $totalNews = $stats->getNewsCount();
        $lastMovies = $repo->findBy([], ['id' => 'DESC'], 3);
        //trouver les utilisateurs par roles
        $roles = ['ROLE_MODERATEUR', 'ROLE_REDACTEUR'];
        $users = $userRepo->findByRoles($roles);

        $latestReviews = $reviewsRepo->findBy([], ['createdAt' => 'DESC'], 10);
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
     * Affiche la page d'accueil
     *
     * @param MediaRepository $repo
     * @param StatsService $stats
     * @param UserRepository $userRepo
     * @return Response
     */
    #[Route('/legals', name: 'legals')]
    public function legals(): Response
    {

     
        return $this->render('legals/index.html.twig');
    }

    
}
