<?php

namespace App\Controller;

use App\Entity\Actor;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ActorController extends AbstractController
{
    /**
     * Page d'acteur
     *
     * @param Actor $actor
     * @return Response
     */
    #[Route('/actor/{slug}', name: 'actor_show')]
    public function show(Actor $actor): Response
    {
        $castings=$actor->getCastings();
        $films = [];
        foreach ($castings as $casting) {
            $films[] = $casting->getMedia();
        }
        return $this->render('actor/show.html.twig', [
        'actor' => $actor,
        'films'=>$films
       
        ]);
    }
}
