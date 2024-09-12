<?php

namespace App\Controller;

use App\Entity\Media;
use App\Service\PaginationService;
use App\Repository\MediaRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MovieController extends AbstractController
{
    #[Route('/medias/{page<\d+>?1}', name: 'medias')]
    public function index(Request $request, MediaRepository $repo, PaginationService $pagination, int $page = 1): Response
    {
        $search = $request->query->get('search', '');

        if ($search) {
            $queryBuilder = $repo->createQueryBuilder('m')
                ->where('m.title LIKE :search')
                ->setParameter('search', '%' . $search . '%');
            $pagination->setDataSource($queryBuilder)->setPage($page)->setLimit(9)->setRoute('medias');
        } else {
            $pagination->setDataSource(Media::class)->setPage($page)->setLimit(9)->setRoute('medias');
        }

        $medias = $pagination->getData();

        return $this->render('media/index.html.twig', [
            'pagination' => $pagination,
            'medias' => $medias,
            'search' => $search,
        ]);
    }
}
