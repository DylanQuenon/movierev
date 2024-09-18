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
    #[Route('/', name: 'home')]
    public function index(MediaRepository $repo, StatsService $stats, UserRepository $userRepo): Response
    {
        $totalMedia = $stats->getMediaCount();
        $lastMovies = $repo->findBy([], ['id' => 'DESC'], 3);


        $roles = ['ROLE_MODERATEUR', 'ROLE_REDACTEUR'];
        $users = $userRepo->findByRoles($roles);
     

      
        // dd($$userRepo->findUsersByRoles('ROLE_MODERATEUR'));


        return $this->render('home.html.twig', [
            'medias'=> $lastMovies,
            'stats' => [
                'allMedias' => $totalMedia,
         
            ],
            'teams' => $users, 
        ]);
    }
}
