<?php

namespace App\Controller;

use App\Service\StatsService;
use App\Repository\MediaRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(MediaRepository $repo, StatsService $stats): Response
    {
        $totalMedia = $stats->getMediaCount();
        $lastMovies = $repo->findBy([], ['id' => 'DESC'], 3);

        return $this->render('home.html.twig', [
            'medias'=> $lastMovies,
            'stats' => [
                'allMedias' => $totalMedia,
         
            ],
        ]);
    }
}
