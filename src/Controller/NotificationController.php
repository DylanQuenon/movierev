<?php

namespace App\Controller;

use App\Service\PaginationService;
use App\Repository\ReviewRepository;
use App\Repository\CommentRepository;
use App\Repository\NotificationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NotificationController extends AbstractController
{
    #[Route('account/notifications', name: 'notifications_index')]
    #[IsGranted('ROLE_USER')]
    public function index(NotificationRepository $notificationRepo, Request $request, ReviewRepository $reviewRepo, CommentRepository $commentRepo, PaginatorInterface $paginator, int $page=1 ): Response
    {
        $user = $this->getUser();

        // Récupérer toutes les reviews de l'utilisateur
        $reviews = $reviewRepo->findBy(['author' => $user]);
        $comments = $commentRepo->findBy(['author' => $user]);

        // Compter les notifications non lues
        $unreadCount = $notificationRepo->countUnreadNotifications($user);
        $unreadCountLikes = $notificationRepo->countUnreadLikesNotifications($user, $reviews, $comments);
        $unreadCountComments = $notificationRepo->countUnreadCommentsNotifications($user);
        $unreadCountFollows = $notificationRepo->countUnreadFollowsNotifications($user);
        $unreadCountReviews = $notificationRepo->countUnreadReviewsNotifications($user);

        // Récupérer les notifications
        $notifications = $notificationRepo->getAllNotifications($user);
        // Pagination avec KnpPaginator
        $notifpagin = $paginator->paginate(
            $notifications, // La requête
            $request->query->getInt('page', $page), // Numéro de la page
            10// Nombre de résultats par page
        );

        

        return $this->render('account/notifications/index.html.twig', [
            'notifications' => $notifpagin,
            'unreadCount' => $unreadCount,
            'unreadCountLikes' => $unreadCountLikes,
            'unreadCountComments' => $unreadCountComments,
            'unreadCountFollows' => $unreadCountFollows,
            'unreadCountReviews' => $unreadCountReviews,
        ]);
    }

    #[Route('/account/notifications/likes', name: 'notifications_likes')]
    #[IsGranted('ROLE_USER')]
    public function likes(NotificationRepository $notificationRepo, ReviewRepository $reviewRepo, CommentRepository $commentRepo, Request $request, PaginatorInterface $paginator, int $page=1): Response
    {
        $user = $this->getUser();
        $reviews = $reviewRepo->findBy(['author' => $user]);
        $comments = $commentRepo->findBy(['author' => $user]);
        $notifications = $notificationRepo->getLikesNotifications($reviews, $comments);
        
        // Pagination avec KnpPaginator
        $notifpagin = $paginator->paginate(
            $notifications,
            $request->query->getInt('page', $page),
            10
        );

        // Compter les notifications non lues
        $unreadCount = $notificationRepo->countUnreadNotifications($user);
        $unreadCountLikes = $notificationRepo->countUnreadLikesNotifications($user, $reviews, $comments);
        $unreadCountComments = $notificationRepo->countUnreadCommentsNotifications($user);
        $unreadCountFollows = $notificationRepo->countUnreadFollowsNotifications($user);
        $unreadCountReviews = $notificationRepo->countUnreadReviewsNotifications($user);


        return $this->render('account/notifications/index.html.twig', [
            'notifications' => $notifpagin,
            'unreadCountLikes' => $unreadCountLikes,
            'unreadCountComments' => $unreadCountComments,
            'unreadCountFollows' => $unreadCountFollows,
            'unreadCount'=>$unreadCount,
            'unreadCountReviews' => $unreadCountReviews,
        ]);
    }

    #[Route('/account/notifications/comments', name: 'notifications_comments')]
    #[IsGranted('ROLE_USER')]
    public function comments(NotificationRepository $notificationRepo,  CommentRepository $commentRepo, ReviewRepository $reviewRepo, Request $request, PaginatorInterface $paginator, int $page=1): Response
    {
        $user = $this->getUser();
        $notifications = $notificationRepo->getCommentsNotifications($user);
        
        // Pagination avec KnpPaginator
        $notifpagin = $paginator->paginate(
            $notifications,
            $request->query->getInt('page', $page),
            10
        );
        
        // Récupérer toutes les reviews de l'utilisateur
        $reviews = $reviewRepo->findBy(['author' => $user]);
        $comments = $commentRepo->findBy(['author' => $user]);

        // Compter les notifications non lues
        $unreadCount = $notificationRepo->countUnreadNotifications($user);
        $unreadCountComments = $notificationRepo->countUnreadCommentsNotifications($user); 
        $unreadCountLikes = $notificationRepo->countUnreadLikesNotifications($user, $reviews, $comments);
        $unreadCountFollows = $notificationRepo->countUnreadFollowsNotifications($user);
        $unreadCountReviews = $notificationRepo->countUnreadReviewsNotifications($user);

        return $this->render('account/notifications/index.html.twig', [
            'notifications' => $notifpagin,
            'unreadCountComments' => $unreadCountComments,
            'unreadCountLikes' => $unreadCountLikes,
            'unreadCountFollows' => $unreadCountFollows,
            'unreadCount'=>$unreadCount,
            'unreadCountReviews' => $unreadCountReviews,
        ]);
    }

    #[Route('account/notifications/follows', name: 'notifications_follows')]
    #[IsGranted('ROLE_USER')]
    public function follows(NotificationRepository $notificationRepo,  CommentRepository $commentRepo, ReviewRepository $reviewRepo, Request $request, PaginatorInterface $paginator, int $page=1): Response
    {
        $user = $this->getUser();
        $notifications = $notificationRepo->getReviewsNotifications($user);
                
        // Pagination avec KnpPaginator
        $notifpagin = $paginator->paginate(
            $notifications,
            $request->query->getInt('page', $page),
            10
        );
        
        // Récupérer toutes les reviews de l'utilisateur
        $reviews = $reviewRepo->findBy(['author' => $user]);
        $comments = $commentRepo->findBy(['author' => $user]);

        // Compter les notifications non lues
        $unreadCount = $notificationRepo->countUnreadNotifications($user);
        $unreadCountFollows = $notificationRepo->countUnreadFollowsNotifications($user);
       
        $unreadCountLikes = $notificationRepo->countUnreadLikesNotifications($user, $reviews, $comments);
        $unreadCountComments = $notificationRepo->countUnreadCommentsNotifications($user);
        $unreadCountReviews = $notificationRepo->countUnreadReviewsNotifications($user);

        return $this->render('account/notifications/index.html.twig', [
            'notifications' => $notifpagin,
            'unreadCountFollows' => $unreadCountFollows,
            'unreadCountLikes' => $unreadCountLikes,
            'unreadCountComments' => $unreadCountComments,
            'unreadCount'=>$unreadCount,
            'unreadCountReviews' => $unreadCountReviews,
        ]);
    }
    #[Route('account/notifications/reviews', name: 'notifications_reviews')]
    #[IsGranted('ROLE_USER')]
    public function reviews(NotificationRepository $notificationRepo,  CommentRepository $commentRepo, ReviewRepository $reviewRepo, Request $request, PaginatorInterface $paginator, int $page=1): Response
    {
        $user = $this->getUser();
        $notifications = $notificationRepo->getFollowsNotifications($user);
                
        // Pagination avec KnpPaginator
        $notifpagin = $paginator->paginate(
            $notifications,
            $request->query->getInt('page', $page),
            10
        );
        
        // Récupérer toutes les reviews de l'utilisateur
        $reviews = $reviewRepo->findBy(['author' => $user]);
        $comments = $commentRepo->findBy(['author' => $user]);

        // Compter les notifications non lues
        $unreadCount = $notificationRepo->countUnreadNotifications($user);
        $unreadCountFollows = $notificationRepo->countUnreadFollowsNotifications($user);
       
        $unreadCountLikes = $notificationRepo->countUnreadLikesNotifications($user, $reviews, $comments);
        $unreadCountComments = $notificationRepo->countUnreadCommentsNotifications($user);
        $unreadCountReviews = $notificationRepo->countUnreadReviewsNotifications($user);

        return $this->render('account/notifications/index.html.twig', [
            'notifications' => $notifpagin,
            'unreadCountFollows' => $unreadCountFollows,
            'unreadCountLikes' => $unreadCountLikes,
            'unreadCountComments' => $unreadCountComments,
            'unreadCount'=>$unreadCount,
            'unreadCountReviews' => $unreadCountReviews,
        ]);
    }

    #[Route('/notifications/mark-read', name: 'mark_notifications_read')]
    #[IsGranted('ROLE_USER')]
    public function markRead(NotificationRepository $notificationRepo, ReviewRepository $repo): Response
    {
        $user = $this->getUser();
        $notificationRepo->markAllNotificationsAsRead($user);

        return $this->redirectToRoute('notifications_index');
    }

    #[Route('/notifications/mark-likes-read', name: 'mark_likes_read')]
    #[IsGranted('ROLE_USER')]
    public function markLikesRead(NotificationRepository $notificationRepo, ReviewRepository $reviewRepo): Response
    {
        $user = $this->getUser();
        $notificationRepo->markLikesAsRead($user);

        return $this->redirectToRoute('notifications_likes');
    }

    #[Route('/notifications/mark-follows-read', name: 'mark_follows_read')]
    #[IsGranted('ROLE_USER')]
    public function markFollowsRead(NotificationRepository $notificationRepo): Response
    {
        $user = $this->getUser();
        
        $notificationRepo->markFollowsAsRead($user);

        return $this->redirectToRoute('notifications_follows');
    }
    #[Route('/notifications/mark-follows-read', name: 'mark_follows_read')]
    #[IsGranted('ROLE_USER')]
    public function markReviewsRead(NotificationRepository $notificationRepo, ReviewRepository $reviewRepo): Response
    {
        $user = $this->getUser();
        
        $notificationRepo->markFollowsAsRead($user);

        return $this->redirectToRoute('notifications_reviews');
    }

    #[Route('/notifications/mark-comments-read', name: 'mark_comments_read')]
    #[IsGranted('ROLE_USER')]
    public function markCommentsRead(NotificationRepository $notificationRepo): Response
    {
        $user = $this->getUser();
        
        $notificationRepo->markCommentsAsRead($user);

        return $this->redirectToRoute('notifications_comments');
    }


}