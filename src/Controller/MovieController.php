<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Entity\Media;
use App\Form\ActorType;
use App\Form\MediaType;
use App\Service\PaginationService;
use App\Repository\GenreRepository;
use App\Repository\MediaRepository;
use App\Repository\ReviewRepository;
use App\Service\FileUploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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
        // Récupère l'utilisateur connecté
        $user = $this->getUser();
        
        // Initialisation d'un tableau pour stocker tous les médias
        $allMedia = [];

        if ($user) {
            // Récupère les collections de l'utilisateur
            $collections = $user->getCollections(); // Assure-toi que cette méthode existe dans l'entité User
            
            // Parcours chaque collection pour récupérer les médias
            foreach ($collections as $collection) {
                $collectionMedias = $collection->getCollectionsMedia(); // Assure-toi que cette méthode existe dans l'entité Collection
                
                foreach ($collectionMedias as $collectionMedia) {
                    $media = $collectionMedia->getMedias(); // Récupère le média associé
                    if ($media) {
                        $allMedia[] = $media; // Ajoute le média au tableau
                    }
                }
            }
        }
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
            'allMedia' => $allMedia, // Ajoute le tableau de tous les médias à la vue
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
     * Créer un nouveau media
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param FileUploaderService $fileUploader
     * @return Response
     */
    #[Route("/medias/new", name:"medias_new")]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EntityManagerInterface $manager, FileUploaderService $fileUploader): Response
    {
        $media = new Media();
        $form = $this->createForm(MediaType::class, $media);     
        $form->handleRequest($request);
        $actorForm = $this->createForm(ActorType::class, new Actor());


        if($form->isSubmitted() && $form->isValid())
        {
            $file = $form['cover']->getData();
            if($file){
                $imageName = $fileUploader->upload($file);
                $media->setCover($imageName);
            }

            $file2 = $form['poster']->getData();
            if($file2){
                $imageName = $fileUploader->upload($file2);
                $media->setPoster($imageName);
            }

           
            foreach($media->getCastings() as $casting)
            {
                $casting->setMedia($media);
                $manager->persist($casting);
            }

            // je persiste mon objet media
            $manager->persist($media);
            $manager->flush();
        
            $this->addFlash(
                'success', 
                "Le Media <strong>".$media->getTitle()."</strong> a bien été enregistré"
            );

            return $this->redirectToRoute('medias',[
                'slug' => $media->getSlug()
            ]);
        }

        return $this->render("media/add.html.twig",[
            'myForm' => $form->createView(),
            'actorForm' => $actorForm->createView(),
        ]);
    }

    /**
     * Nouvel acteur
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param FileUploaderService $fileUploader
     * @return Response
     */
    #[Route("/actors/new", name:"actors_new")]
    #[IsGranted('ROLE_USER')]
    public function createActor(Request $request, EntityManagerInterface $manager, FileUploaderService $fileUploader): Response
    {
        $actor = new Actor();
        $form = $this->createForm(ActorType::class, $actor);     
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            
                        $existingActor = $manager->getRepository(Actor::class)->findOneBy([
                            'firstName' => $actor->getFirstName(),
                            'name' => $actor->getName()
                        ]);
            
                        if ($existingActor) {
                            $this->addFlash(
                                'error',
                                "L'acteur <strong>" . $actor->getFullName() . "</strong> existe déjà"
                            );
                            return $this->redirectToRoute('medias_new');
                        }
            
            $file = $form['picture']->getData();
            if($file){
                $imageName = $fileUploader->upload($file);
                $actor->setPicture($imageName);
            }
            // je persiste mon objet actor
            $manager->persist($actor);
            $manager->flush();
          
            $this->addFlash(
                'success', 
                "L'acteur <strong>".$actor->getFullName()."</strong> a bien été enregistré"
            );

            return $this->redirectToRoute('medias_new');
        }

    }

  
    /**
     * Affiche page individuelle
     *
     * @param Media $media
     * @param MediaRepository $repo
     * @param ReviewRepository $reviewRepository
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
           // Initialisation d'un tableau pour stocker tous les médias
        $allMedia = [];
        $user=$this->getUser();
        if ($user) {
            // Récupère les collections de l'utilisateur
            $collections = $user->getCollections(); // 
            
            // Parcours chaque collection pour récupérer les médias
            foreach ($collections as $collection) {
                $collectionMedias = $collection->getCollectionsMedia();
                
                foreach ($collectionMedias as $collectionMedia) {
                    $mediaContent = $collectionMedia->getMedias(); // Récupère le média associé
                    if ($mediaContent) {
                        $allMedia[] = $mediaContent; // Ajoute le média au tableau
                    }
                }
            }
        }
        return $this->render('media/show.html.twig', [
            'media' => $media,
            'topReview' => $mostLikedReviews,
            'latestMovies' => $latestMovies,
            'allMedia' => $allMedia,
        ]);

    } 

 
}
