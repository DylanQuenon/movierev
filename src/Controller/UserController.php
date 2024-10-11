<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\NotificationRepository;
use App\Repository\SubscriptionRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{

        /**
     * Effectue la recherche ajax pour prendre les news
     *
     * @param Request $request
     * @param NewsRepository $newsRepo
     * @return JsonResponse
     */
    #[Route('/users/search/ajax', name: 'user_search_ajax', methods: ['GET'])]
    public function searchAjax(Request $request, UserRepository $repo): JsonResponse
    {
        $query = $request->query->get('query', '');

        if (empty($query)) {
            return new JsonResponse([]); // Renvoie un tableau vide si aucun terme
        }

        $results = $repo->findByUsername($query)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $jsonResults = array_map(function ($users) {
            return [
                'fullName' => $users->getFullName(),
                'username' => $users->getUsername(),
                'slug' => $users->getSlug(),
                'avatar'=>$users->getAvatar(),
            ];
        }, $results);

        return new JsonResponse($jsonResults);
    }
    /**
     * Affiche le profil utilisateur
     *
     * @param User $user
     * @param UserRepository $repo
     * @param SubscriptionRepository $followingRepo
     * @return Response
     */
    #[Route('/user/{slug}', name: 'user_show')]
    public function index(User $user, UserRepository $repo, SubscriptionRepository $followingRepo): Response
    {
          // Votre logique pour récupérer l'utilisateur par le slug
          $slug = $user->getSlug();
          $isPrivate = $user->getIsPrivate() && $this->getUser() !== $user;
          $isFollowing = ($this->getUser() && $followingRepo->isFollowing($this->getUser(), $user));


          if ($user) {
              // Redirection vers la section Reviews
              return $this->redirectToRoute('user_reviews', ['slug' => $slug]);
          }
  
       
        return $this->render('user/index.html.twig', [
            'user' => $user,
            
          
        ]);
    }

    /**
     * A propos
     *
     * @param User $user
     * @param UserRepository $repo
     * @param SubscriptionRepository $followingRepo
     * @param NotificationRepository $notificationRepo
     * @return Response
     */
    #[Route('/user/{slug}/about', name: 'user_about')]
    public function about(User $user, UserRepository $repo, SubscriptionRepository $followingRepo,NotificationRepository $notificationRepo,): Response
    {
        $isPrivate = $user->getIsPrivate() && $this->getUser() !== $user;
        $isFollowing = ($this->getUser() && $followingRepo->isFollowing($this->getUser(), $user));
        
            $notifications = $notificationRepo->getAllNotifications($this->getUser(),5);
            $unreadCount = $notificationRepo->countUnreadNotifications($this->getUser());
        
       
        return $this->render('user/tab/about.html.twig', [
            'user' => $user,
            'isFollowing' => $isFollowing,
            'isPrivate' => $isPrivate,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
          
        ]);
    }
    /**
     * Reviews de l'utilisateur
     *
     * @param User $user
     * @param Request $request
     * @param UserRepository $repo
     * @param PaginatorInterface $paginator
     * @param NotificationRepository $notificationRepo
     * @param SubscriptionRepository $followingRepo
     * @param integer $page
     * @return Response
     */
    #[Route('/user/{slug}/reviews/{page<\d+>?1}', name: 'user_reviews')]
    public function userReviews(User $user,Request $request, UserRepository $repo, PaginatorInterface $paginator, NotificationRepository $notificationRepo, SubscriptionRepository $followingRepo, int $page = 1): Response
    {
        $isPrivate = $user->getIsPrivate() && $this->getUser() !== $user;
        $isFollowing = ($this->getUser() && $followingRepo->isFollowing($this->getUser(), $user));
        $allReviews= $user->getReviews();
        
            $notifications = $notificationRepo->getAllNotifications($this->getUser(),5);
            $unreadCount = $notificationRepo->countUnreadNotifications($this->getUser());
        
       
        // Pagination avec KnpPaginator
        $reviews = $paginator->paginate(
            $allReviews, // La requête
            $request->query->getInt('page', $page), // Numéro de la page
            9 // Nombre de résultats par page
        );

        return $this->render('user/tab/reviews.html.twig', [
            'user' => $user,
            'reviews' => $reviews,
            'isFollowing' => $isFollowing,
            'isPrivate' => $isPrivate,
            'unreadCount' => $unreadCount,
            'notifications' => $notifications,
          
        ]);
    }

    /**
     * Likes de l'utilisateur
     *
     * @param User $user
     * @param Request $request
     * @param UserRepository $repo
     * @param NotificationRepository $notificationRepo
     * @param PaginatorInterface $paginator
     * @param SubscriptionRepository $followingRepo
     * @param integer $page
     * @return Response
     */
    #[Route('/user/{slug}/likes/{page<\d+>?1}', name: 'user_likes')]
    #[IsGranted(
        attribute: new Expression('user === subject and is_granted("ROLE_USER")'),
        subject: new Expression('args["user"]'), // Assurez-vous que le sujet est l'utilisateur correct
        message: "Vous n'avez pas accès à cette page"
    )]
    public function userLikes(User $user, Request $request, UserRepository $repo,NotificationRepository $notificationRepo, PaginatorInterface $paginator,SubscriptionRepository $followingRepo, int $page = 1): Response
    {
        // Récupérer toutes les reviews que l'utilisateur a aimée

        $likedLikes = $user->getLikes(); // Récupère tous les Likes de l'utilisateur

        // Initialisation d'un tableau pour stocker les Reviews
        $likedReviews = [];
        $isPrivate = $user->getIsPrivate() && $this->getUser() !== $user;
        $isFollowing = ($this->getUser() && $followingRepo->isFollowing($this->getUser(), $user));
    
        // Boucle sur chaque Like pour récupérer les Reviews
        foreach ($likedLikes as $like) {
            $review = $like->getReview(); // Récupère la Review associée à ce Like
            if ($review) {
                $likedReviews[] = $review; // Ajoute la Review au tableau si elle existe
            }
        }


        // Pagination avec KnpPaginator
        $likedReviews = $paginator->paginate(
            $likedReviews, // La requête
            $request->query->getInt('page', $page), // Numéro de la page
            9 // Nombre de résultats par page
        );

        $notifications = $notificationRepo->getAllNotifications($this->getUser(),5);
        $unreadCount = $notificationRepo->countUnreadNotifications($this->getUser());
    
        return $this->render('user/tab/likes.html.twig', [
            'user' => $user,
            'reviews' => $likedReviews,
            'isFollowing' => $isFollowing,
            'isPrivate' => $isPrivate,
            'unreadCount' => $unreadCount,
            'notifications' => $notifications,
            
        ]);
    }

    /**
     * Actu écrite par le user
     *
     * @param User $user
     * @param Request $request
     * @param UserRepository $repo
     * @param PaginatorInterface $paginator
     * @param SubscriptionRepository $followingRepo
     * @param NotificationRepository $notificationRepo
     * @param integer $page
     * @return Response
     */
    #[Route('/user/{slug}/news/{page<\d+>?1}', name: 'user_news')]
    public function userNews(User $user,Request $request, UserRepository $repo, PaginatorInterface $paginator, SubscriptionRepository $followingRepo, NotificationRepository $notificationRepo, int $page = 1): Response
    {
        if (!in_array('ROLE_REDACTEUR', $user->getRoles())) {
            return $this->redirectToRoute('user_show', ['slug' => $user->getSlug()]);
        }
        
        $allNews= $user->getNews();
        $isPrivate = $user->getIsPrivate() && $this->getUser() !== $user;
        $isFollowing = ($this->getUser() && $followingRepo->isFollowing($this->getUser(), $user));

        
            $notifications = $notificationRepo->getAllNotifications($this->getUser(),5);
            $unreadCount = $notificationRepo->countUnreadNotifications($this->getUser());
        
       
        // Pagination avec KnpPaginator
        $news = $paginator->paginate(
            $allNews, // La requête
            $request->query->getInt('page', $page), // Numéro de la page
            9 // Nombre de résultats par page
        );

        return $this->render('user/tab/articles.html.twig', [
            'user' => $user,
            'articles' => $news,
            'isFollowing' => $isFollowing,
            'isPrivate' => $isPrivate,
            'unreadCount' => $unreadCount,
            'notifications' => $notifications,
          
        ]);
    }

    

    /**
     * Mon compte
     *
     * @param UserRepository $repo
     * @return Response
     */
    #[Route('/my-account', name: 'account_index')]
    #[IsGranted('ROLE_USER')]
    public function account(UserRepository $repo): Response
    {
          // Votre logique pour récupérer l'utilisateur par le slug
          $slug = $this->getUser()->getSlug();

          if ($this->getUser()) {
              // Redirection vers la section Reviews
              return $this->redirectToRoute('user_reviews', ['slug' => $slug]);
          }
  
       
        return $this->render('user/index.html.twig', [
            'user' => $this->getUser(),
          
        ]);
    }
    
}
