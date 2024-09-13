<?php

namespace App\Controller;

use App\Entity\Media;
use App\Service\PaginationService;
use App\Repository\MediaRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MovieController extends AbstractController
{
    #[Route('/medias/{page<\d+>?1}', name: 'medias')]
    public function index(Request $request, MediaRepository $repo, PaginationService $pagination, int $page): Response
    {
    
        $pagination->setDataSource(Media::class)->setPage($page)->setLimit(8)->setRoute('medias');
        $medias = $pagination->getData();

        return $this->render('media/index.html.twig', [
            'pagination' => $pagination,
            'medias' => $medias,
            
        ]);
    }

    #[Route('/medias/search/ajax', name: 'medias_search_ajax', methods: ['GET'])]
    public function searchAjax(Request $request, MediaRepository $repo): JsonResponse
    {
        $query = $request->query->get('query', '');

        if (empty($query)) {
            return new JsonResponse([]); // Renvoie un tableau vide si aucun terme
        }

        $results = $repo->findByTitle($query)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $jsonResults = array_map(function ($media) {
            return [
                'title' => $media->getTitle(),
                'slug' => $media->getSlug(),
                'poster' => $media->getPoster(),
                'synopsis' => substr($media->getSynopsis(), 0, 50) . "...",
                'releaseDate' => $media->getReleaseDate()->format('Y'),
            ];
        }, $results);

        return new JsonResponse($jsonResults);
    }
}
