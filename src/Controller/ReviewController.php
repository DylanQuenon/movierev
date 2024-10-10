<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Likes;
use App\Entity\Media;
use App\Entity\Review;
use App\Entity\Comment;
use App\Form\ReplyType;
use App\Form\CommentType;
use App\Form\ReviewsType;
use App\Repository\LikesRepository;
use App\Repository\ReviewRepository;
use App\Service\NotificationService;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SubscriptionRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReviewController extends AbstractController
{
    #[Route('/reviews/{id}/like', name: 'reviews_like', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function like(Request $request, NotificationService $notifService, Review $review, EntityManagerInterface $manager): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $like = $manager->getRepository(Likes::class)->findOneBy(['author' => $user, 'review' => $review]);
    

        if ($like) {
            // Si l'utilisateur a déjà aimé, on le retire (dislike)
            $manager->remove($like);
            $action = 'disliked';
        } else {
            // Sinon, on ajoute un nouveau like
            $like = new Likes();
            $like->setAuthor($user);
            $like->setReview($review);
            $manager->persist($like);
            $action = 'liked';
                  // Appeler le service de notification ici
        $notifService->addNotification(
            'like', 
            $user, 
            $review->getAuthor(), // Utilisateur qui a posté la review
            $review // On passe la review à la notification
        );
        }

        $manager->flush();

        return new JsonResponse(['action' => $action, 'likesCount' => $review->getLikes()->count()]);
    }


    #[Route('/reviews/search/ajax', name: 'reviews_search_ajax', methods: ['GET'])]
    public function searchAjax(Request $request, ReviewRepository $repo): JsonResponse
    {
        $query = $request->query->get('query', '');

        if (empty($query)) {
            return new JsonResponse([]); // Renvoie un tableau vide si aucun terme
        }

        $results = $repo->findReviewsByMediaTitle($query);

        // Mappez les résultats pour créer le format JSON
        $jsonResults = array_map(function ($review) {
            return [
                'title' => $review->getTitle(),
                'author' => $review->getAuthor()->getFullName(),
                'mediatitle' => $review->getMedia()->getTitle(),
                'slug' => $review->getSlug(),
            ];
        }, $results);
    
        return new JsonResponse($jsonResults);
    }
    /**
     * Affiche les reviews
     *
     * @param ReviewRepository $repo
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param string $filterType
     * @param integer $page
     * @return Response
     */
    #[Route('/reviews/{filterType}/page/{page<\d+>?1}', name: 'reviews_index')]
    public function index( ReviewRepository $repo, Request $request, PaginatorInterface $paginator,string $filterType = 'general', int $page = 1): Response
    {
        $currentUser = $this->getUser();
    
        // Selon le type de filtre, récupère les critiques
        if ($filterType === 'suivis' && $currentUser) {
            // Critiques des utilisateurs suivis seulement
            $query = $repo->findReviewsFromFollowedUsers($currentUser);
         
        } elseif ($filterType === 'general') {
            // Critiques générales (toutes les critiques publiques)
            $query = $repo->findPublicReviews();
        
        } else {
            // Si aucun des deux filtres n'est valide, on redirige vers une page 404
            throw $this->createNotFoundException('La page demandée est introuvable.');
        }
 
        
       
        // Pagination avec KnpPaginator
        $reviews = $paginator->paginate(
            $query, // La requête
            $request->query->getInt('page', $page), // Numéro de la page
            9 // Nombre de résultats par page
        );
    
        return $this->render('review/index.html.twig', [
            'reviews' => $reviews,
            'filterType' => $filterType,
        ]);
    }

    /**
     * Affiche les reviews indiv
     *
     * @param string $slug
     * @param ReviewRepository $repo
     * @param Review $reviews
     * @param Request $request
     * @param SubscriptionRepository $subrepo
     * @param CommentRepository $commentRepo
     * @param EntityManagerInterface $manager
     * @param LikesRepository $repoLikes
     * @return Response
     */
    #[Route("/reviews/{slug}", name:"reviews_show")]
    public function show(string $slug, ReviewRepository $repo, Review $reviews, Request $request, SubscriptionRepository $subrepo, CommentRepository $commentRepo, EntityManagerInterface $manager,LikesRepository $repoLikes): Response
    {

        $author = $reviews->getAuthor();
    
        // Si l'utilisateur connecté n'est pas l'auteur et que le compte est privé, vérifiez l'abonnement
        if ($author->getIsPrivate() && $this->getUser() !== $author && !$subrepo->isFollowing($this->getUser(), $author)) {
            return $this->redirectToRoute('home'); // Remplacez 'home' par le nom de votre route pour la page d'accueil
        }
    
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        
        $latestReviews = $repo->findPublicReviews(10);
 
    

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifie si l'utilisateur essaie de commenter sa propre review
            if ($this->getUser() === $reviews->getAuthor()) {
                $this->addFlash('warning', 'Vous ne pouvez pas commenter votre propre review.');
                return $this->redirectToRoute('reviews_show', ['slug' => $reviews->getSlug()]);
            }

            $comment->setReview($reviews)  // Associe la review au commentaire
                    ->setAuthor($this->getUser());  // Associe l'auteur

                 

            // Persiste le commentaire
            $manager->persist($comment);
            $manager->flush();
            $form = $this->createForm(CommentType::class);

            $this->addFlash('success', 'Votre commentaire a été pris en compte.');
        }

        $comments= $reviews->getComments();
        $topComment = null;
        if (count($comments) > 0) {
            $topComment = array_reduce($comments->toArray(), function ($carry, $item) {
                return ($carry === null || $item->getLikes()->count() > $carry->getLikes()->count()) ? $item : $carry;
            });
        }
        $replyForms = [];
        foreach ($comments as $comment) {
            if ($comment->getParent() === null) {
                $replyForm = $this->createForm(ReplyType::class, new Comment(), [
                    'parent' => $comment,
                ]);
                $replyForms[$comment->getId()] = $replyForm->createView();
            }
        }
        $likedByUsers = $repoLikes->findBy(['review' => $reviews]);

        return $this->render("review/show.html.twig", [
            'review' => $reviews,
            'latestReviews' => $latestReviews,
            'myForm' => $form->createView(),
            'replyForms' => $replyForms,
            'topComment' => $topComment,
            'likedByUsers' => $likedByUsers,
            
            
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
    #[IsGranted('ROLE_USER', message: "Vous devez être connecté pour poster un commentaire.")]
    public function create($mediaSlug, Request $request, EntityManagerInterface $manager, NotificationService $notifService): Response
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
                   ->setMedia($media); 
    
                   $manager->persist($review);
                   $manager->flush();
                   $this->addFlash(
                       'success', 
                       "Votre review a bien été publiée"
                    );
                    $subscribers = $review->getAuthor()->getFolloweds();
                        // Envoyer une notification à chaque abonné
                        foreach ($subscribers as $subscriber) {
                            $notifService->addNotification(
                                'review',
                                $review->getAuthor(),
                                $subscriber->getFollower(), // L'abonné qui reçoit la notification
                                $review // Passer la review elle-même
                            );
                        }
    
            // // Redirection vers une page pertinente après la soumission, par exemple la page du média
            return $this->redirectToRoute('reviews_show', [
                'slug' => $review->getSlug() // Assurez-vous que la route 'media_show' existe
            ]);
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
#[Route('/reviews/edit/{slug}', name: 'reviews_edit')]
#[IsGranted(
    attribute: new Expression('(user === subject and is_granted("ROLE_USER")) or is_granted("ROLE_ADMIN")'),
    subject: new Expression('args["review"].getAuthor()'),
    message: "La review ne vous appartient pas, vous ne pouvez pas la modifier"
)]
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
#[Route('/reviews/{slug}/delete', name: 'review_delete')]
#[IsGranted(
    attribute: new Expression('(user === subject and is_granted("ROLE_USER")) or is_granted("ROLE_ADMIN")'),
    subject: new Expression('args["review"].getAuthor()'),
    message: "La review ne vous appartient pas, vous ne pouvez pas l'effacer"
)]
public function delete(Review $review, EntityManagerInterface $manager, Request $request): Response
{
    
        $manager->remove($review);
        $manager->flush();

        $this->addFlash('success', 'La review a été supprimée avec succès.');
    

    return $this->redirectToRoute('home');
}

    
    
}
