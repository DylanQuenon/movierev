<?php

namespace App\Controller;

use App\Service\StatsService;
use App\Repository\UserRepository;
use App\Repository\MediaRepository;
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
    public function index(MediaRepository $repo, StatsService $stats, UserRepository $userRepo): Response
    {
        $totalMedia = $stats->getMediaCount();
        $totalNews = $stats->getNewsCount();
        $lastMovies = $repo->findBy([], ['id' => 'DESC'], 3);
        //trouver les utilisateurs par roles
        $roles = ['ROLE_MODERATEUR', 'ROLE_REDACTEUR'];
        $users = $userRepo->findByRoles($roles);
     
        return $this->render('home.html.twig', [
            'medias'=> $lastMovies,
            'stats' => [
                'allMedias' => $totalMedia,
                'allNews' => $totalNews,
         
            ],
            'teams' => $users, 
        ]);
    }
}
