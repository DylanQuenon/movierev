<?php

namespace App\Controller;

use App\Entity\Media;
use App\Service\PaginationService;
use App\Repository\GenreRepository;
use App\Repository\MediaRepository;
use App\Repository\ReviewRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MovieController extends AbstractController
{
    /**
     * Afficher les films & séries
     *
     * @param Request $request
     * @param MediaRepository $repo
     * @param GenreRepository $genreRepo
     * @param PaginatorInterface $paginator
     * @param integer $page
     * @return Response
     */
    #[Route('/medias/{page<\d+>?1}', name: 'medias')]
    public function index(Request $request, MediaRepository $repo, GenreRepository $genreRepo, PaginatorInterface $paginator, int $page = 1): Response
    {
        // Récupère le genre et l'ordre depuis les paramètres GET
        $genre = $request->query->get('genre');
        $order = $request->query->get('order', 'newest'); // Valeur par défaut
    
        // Récupère tous les genres pour le filtre
        $genres = $genreRepo->findAll();
    
        // Initialisation de la requête de base
        $queryBuilder = $repo->createQueryBuilder('m');
    
        // Si un genre est sélectionné, on ajoute une condition à la requête
        if ($genre) {
            $queryBuilder
                ->join('m.genres', 'g') // Assure-toi que 'genres' est la relation entre Media et Genre
                ->andWhere('g.name = :genre')
                ->setParameter('genre', $genre);
        }
    
        // Ajoute un tri en fonction de l'ordre choisi
        if ($order === 'oldest') {
            $queryBuilder->orderBy('m.release_date', 'ASC');
        } else {
            $queryBuilder->orderBy('m.release_date', 'DESC');
        }
    
        // Pagination avec KnpPaginator
        $medias = $paginator->paginate(
            $queryBuilder, // La requête
            $request->query->getInt('page', $page), // Numéro de la page
            9 // Nombre de résultats par page
        );
    
        return $this->render('media/index.html.twig', [
            'medias' => $medias,
            'genres' => $genres,
            'currentGenre' => $genre,
            'order' => $order, // Passe l'ordre à la vue
        ]);
    }
    
    /**
     * Recherche des média via appel ajax
     *
     * @param Request $request
     * @param MediaRepository $repo
     * @return JsonResponse
     */
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
                'synopsis' => substr($media->getSynopsis(), 0, 100) . "...",
                'releaseDate' => $media->getReleaseDate()->format('Y'),
            ];
        }, $results);

        return new JsonResponse($jsonResults);
    }

    /**
     * Affichage individuel
     *
     * @param Media $media
     * @return void
     */
    #[Route('/medias/{slug}', name: 'medias_show')]
    public function show(Media $media, MediaRepository $repo, ReviewRepository $reviewRepository){
        $mostLikedReviews = $reviewRepository->findMostLikedReviews($media);

                // Récupère les trois dernières actualités, excluant celle affichée
                $latestMovies = $repo->createQueryBuilder('n')
                ->where('n.id != :currentNewsId')
                ->setParameter('currentNewsId', $media->getId())
                ->orderBy('n.id', 'DESC')
                ->setMaxResults(3)
                ->getQuery()
                ->getResult();
        
    
        return $this->render('media/show.html.twig', [
            'media' => $media,
            'topReview' => $mostLikedReviews,
            'latestMovies' => $latestMovies,
        ]);

    } 
}
