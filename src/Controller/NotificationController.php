<?php

namespace App\Controller;

use App\Service\PaginationService;
use App\Repository\ReviewRepository;
use App\Repository\CommentRepository;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NotificationController extends AbstractController
{
    #[Route('account/notifications', name: 'notifications_index')]
    #[IsGranted('ROLE_USER')]
    public function index(NotificationRepository $notificationRepo, ReviewRepository $reviewRepo, CommentRepository $commentRepo): Response
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

        return $this->render('account/notifications/index.html.twig', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'unreadCountLikes' => $unreadCountLikes,
            'unreadCountComments' => $unreadCountComments,
            'unreadCountFollows' => $unreadCountFollows,
            'unreadCountReviews' => $unreadCountReviews,
        ]);
    }

    #[Route('/account/notifications/likes', name: 'notifications_likes')]
    #[IsGranted('ROLE_USER')]
    public function likes(NotificationRepository $notificationRepo, ReviewRepository $reviewRepo, CommentRepository $commentRepo): Response
    {
        $user = $this->getUser();
        $reviews = $reviewRepo->findBy(['author' => $user]);
        $comments = $commentRepo->findBy(['author' => $user]);
        $notifications = $notificationRepo->getLikesNotifications($reviews, $comments);
        

        // Compter les notifications non lues
        $unreadCount = $notificationRepo->countUnreadNotifications($user);
        $unreadCountLikes = $notificationRepo->countUnreadLikesNotifications($user, $reviews, $comments);
        $unreadCountComments = $notificationRepo->countUnreadCommentsNotifications($user);
        $unreadCountFollows = $notificationRepo->countUnreadFollowsNotifications($user);
        $unreadCountReviews = $notificationRepo->countUnreadReviewsNotifications($user);


        return $this->render('account/notifications/index.html.twig', [
            'notifications' => $notifications,
            'unreadCountLikes' => $unreadCountLikes,
            'unreadCountComments' => $unreadCountComments,
            'unreadCountFollows' => $unreadCountFollows,
            'unreadCount'=>$unreadCount,
            'unreadCountReviews' => $unreadCountReviews,
        ]);
    }

    #[Route('/account/notifications/comments', name: 'notifications_comments')]
    #[IsGranted('ROLE_USER')]
    public function comments(NotificationRepository $notificationRepo,  CommentRepository $commentRepo, ReviewRepository $reviewRepo): Response
    {
        $user = $this->getUser();
        $notifications = $notificationRepo->getCommentsNotifications($user);
        
        // Récupérer toutes les reviews de l'utilisateur
        $reviews = $reviewRepo->findBy(['author' => $user]);
        $comments = $commentRepo->findBy(['author' => $user]);

        // Compter les notifications non lues
                // Compter les notifications non lues
        $unreadCount = $notificationRepo->countUnreadNotifications($user);
        $unreadCountComments = $notificationRepo->countUnreadCommentsNotifications($user); 
        $unreadCountLikes = $notificationRepo->countUnreadLikesNotifications($user, $reviews, $comments);
        $unreadCountFollows = $notificationRepo->countUnreadFollowsNotifications($user);
        $unreadCountReviews = $notificationRepo->countUnreadReviewsNotifications($user);

        return $this->render('account/notifications/index.html.twig', [
            'notifications' => $notifications,
            'unreadCountComments' => $unreadCountComments,
            'unreadCountLikes' => $unreadCountLikes,
            'unreadCountFollows' => $unreadCountFollows,
            'unreadCount'=>$unreadCount,
            'unreadCountReviews' => $unreadCountReviews,
        ]);
    }

    #[Route('account/notifications/follows', name: 'notifications_follows')]
    #[IsGranted('ROLE_USER')]
    public function follows(NotificationRepository $notificationRepo,  CommentRepository $commentRepo, ReviewRepository $reviewRepo): Response
    {
        $user = $this->getUser();
        $notifications = $notificationRepo->getReviewsNotifications($user);
                
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
            'notifications' => $notifications,
            'unreadCountFollows' => $unreadCountFollows,
            'unreadCountLikes' => $unreadCountLikes,
            'unreadCountComments' => $unreadCountComments,
            'unreadCount'=>$unreadCount,
            'unreadCountReviews' => $unreadCountReviews,
        ]);
    }
    #[Route('account/notifications/reviews', name: 'notifications_reviews')]
    #[IsGranted('ROLE_USER')]
    public function reviews(NotificationRepository $notificationRepo,  CommentRepository $commentRepo, ReviewRepository $reviewRepo): Response
    {
        $user = $this->getUser();
        $notifications = $notificationRepo->getFollowsNotifications($user);
                
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
            'notifications' => $notifications,
            'unreadCountFollows' => $unreadCountFollows,
            'unreadCountLikes' => $unreadCountLikes,
            'unreadCountComments' => $unreadCountComments,
            'unreadCount'=>$unreadCount,
            'unreadCountReviews' => $unreadCountReviews,
        ]);
    }
}
