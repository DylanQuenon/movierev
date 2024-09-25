<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Review;
use App\Form\ReviewsType;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReviewController extends AbstractController
{
    #[Route('/reviews/page/{page<\d+>?1}', name: 'reviews_index')]
    public function index( ReviewRepository $repo, Request $request, PaginatorInterface $paginator, int $page = 1): Response
    {
        $allReviews= $repo->findAll();
       
        // Pagination avec KnpPaginator
        $reviews = $paginator->paginate(
            $allReviews, // La requête
            $request->query->getInt('page', $page), // Numéro de la page
            9 // Nombre de résultats par page
        );
    
        return $this->render('review/index.html.twig', [
            'reviews' => $reviews,
        ]);
    }

    #[Route("/reviews/{slug}", name:"reviews_show")]
    public function show(string $slug, ReviewRepository $repo, Review $reviews, Request $request, EntityManagerInterface $manager): Response
    {

        $latestReviews = $repo->findBy([], ['createdAt' => 'DESC'], 10);
    
        return $this->render("review/show.html.twig", [
            'review' => $reviews,
            "latestReviews" => $latestReviews,
        
        ]);
    }
    
    /**
     * Ajout d'une review
     *
     * @param [type] $mediaSlug
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/reviews/add/{mediaSlug}', name: 'reviews_create')]
    public function create($mediaSlug, Request $request, EntityManagerInterface $manager): Response
    {
        // Création d'une nouvelle instance de Review
        $review = new Review();
        $form = $this->createForm(ReviewsType::class, $review);     
        $form->handleRequest($request);
        
        // Récupération du média correspondant au slug
        $media = $manager->getRepository(Media::class)->findOneBy(['slug' => $mediaSlug]);
    
        // Vérification que le média existe
        if (!$media) {
            throw $this->createNotFoundException('Media not found');
        }
    
        if ($form->isSubmitted() && $form->isValid())
        {
            // Associer l'auteur et le média à la review
            $review->setAuthor($this->getUser())
                   ->setMedia($media); // Utilisez $media pour définir le média
    
            // Persist la review dans la base de données
            $manager->persist($review);
            $manager->flush();
    
            $this->addFlash(
                'success', 
                "Votre review a bien été publiée"
            );
    
            // // Redirection vers une page pertinente après la soumission, par exemple la page du média
            // return $this->redirectToRoute('media_show', [
            //     'slug' => $media->getSlug() // Assurez-vous que la route 'media_show' existe
            // ]);
        }
    
        return $this->render("review/add.html.twig", [
            'myForm' => $form->createView(),
            'media' => $media // Vous pouvez passer le média à la vue si nécessaire
        ]);
    }


    /**
     * Modification de la review
     *
     * @param Review $review
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
#[Route('/reviews/edit/{id}', name: 'reviews_edit')]
public function edit(Review $review, Request $request, EntityManagerInterface $manager): Response
{
    $form = $this->createForm(ReviewsType::class, $review);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $review->setSlug('');
        $manager->persist($review);
        $manager->flush();

        $this->addFlash(
            'success',
            "Votre review <strong>".$review->getTitle()."</strong> a bien été modifiée"
        );

        // return $this->redirectToRoute('news_show',[
        //     'slug' => $news->getSlug()
        // ]);
    }

    return $this->render("review/edit.html.twig",[
        "review" => $review,
        "myForm" => $form->createView()
    ]);
}

/**
 * Effacer une review
 *
 * @param Review $review
 * @param EntityManagerInterface $manager
 * @param Request $request
 * @return Response
 */
#[Route('/reviews/{id}/delete', name: 'review_delete', methods: ['POST'])]
public function delete(Review $review, EntityManagerInterface $manager, Request $request): Response
{
    
        $manager->remove($review);
        $manager->flush();

        $this->addFlash('success', 'La review a été supprimée avec succès.');
    

    return $this->redirectToRoute('home');
}

    
    
}
