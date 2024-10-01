<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    #[Route('/user/{slug}', name: 'user_show')]
    public function index(User $user, UserRepository $repo): Response
    {
          // Votre logique pour récupérer l'utilisateur par le slug
          $slug = $user->getSlug();

          if ($user) {
              // Redirection vers la section Reviews
              return $this->redirectToRoute('user_reviews', ['slug' => $slug]);
          }
  
       
        return $this->render('user/index.html.twig', [
            'user' => $user,
          
        ]);
    }

    #[Route('/user/{slug}/about', name: 'user_about')]
    public function about(User $user, UserRepository $repo): Response
    {
        return $this->render('user/tab/about.html.twig', [
            'user' => $user,
          
        ]);
    }
    #[Route('/user/{slug}/reviews/{page<\d+>?1}', name: 'user_reviews')]
    public function userReviews(User $user,Request $request, UserRepository $repo, PaginatorInterface $paginator, int $page = 1): Response
    {
        $allReviews= $user->getReviews();
       
        // Pagination avec KnpPaginator
        $reviews = $paginator->paginate(
            $allReviews, // La requête
            $request->query->getInt('page', $page), // Numéro de la page
            9 // Nombre de résultats par page
        );

        return $this->render('user/tab/reviews.html.twig', [
            'user' => $user,
            'reviews' => $reviews,
          
        ]);
    }

    #[Route('/user/{slug}/likes/{page<\d+>?1}', name: 'user_likes')]
    #[IsGranted(
        attribute: new Expression('user === subject and is_granted("ROLE_USER")'),
        subject: new Expression('args["user"]'), // Assurez-vous que le sujet est l'utilisateur correct
        message: "Vous n'avez pas accès à cette page"
    )]
    public function userLikes(User $user, Request $request, UserRepository $repo, PaginatorInterface $paginator, int $page = 1): Response
    {
        // Récupérer toutes les reviews que l'utilisateur a aimée

        $likedLikes = $user->getLikes(); // Récupère tous les Likes de l'utilisateur

        // Initialisation d'un tableau pour stocker les Reviews
        $likedReviews = [];
    
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
    
        return $this->render('user/tab/likes.html.twig', [
            'user' => $user,
            'reviews' => $likedReviews,
        ]);
    }

    #[Route('/user/{slug}/news/{page<\d+>?1}', name: 'user_news')]
    
    public function userNews(User $user,Request $request, UserRepository $repo, PaginatorInterface $paginator, int $page = 1): Response
    {
        if (!in_array('ROLE_REDACTEUR', $user->getRoles())) {
            return $this->redirectToRoute('user_show', ['slug' => $user->getSlug()]);
        }
        
        $allNews= $user->getNews();
       
        // Pagination avec KnpPaginator
        $news = $paginator->paginate(
            $allNews, // La requête
            $request->query->getInt('page', $page), // Numéro de la page
            9 // Nombre de résultats par page
        );

        return $this->render('user/tab/articles.html.twig', [
            'user' => $user,
            'articles' => $news,
          
        ]);
    }

    

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
