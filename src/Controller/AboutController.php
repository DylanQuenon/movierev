<?php

namespace App\Controller;

use App\Repository\NewsRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AboutController extends AbstractController
{
    #[Route('/about', name: 'about')]
    public function index(UserRepository $userRepo, NewsRepository $newsRepo): Response
    {
        $roles = ['ROLE_MODERATEUR', 'ROLE_REDACTEUR'];
        $users = $userRepo->findByRoles($roles);
        $latestNews = $newsRepo->findBy([], ['createdAt' => 'DESC'], 10);
        return $this->render('about/index.html.twig', [
            'teams' => $users,
            'lastNews' => $latestNews,
        ]);
    }
}
