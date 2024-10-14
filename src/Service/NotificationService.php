<?php

namespace App\Service;

use App\Entity\News;
use App\Entity\User;
use App\Entity\Quizz;
use App\Entity\Review;
use App\Entity\Comment;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function addNotification(string $type, User $user, ?User $relatedUser, ?Review $review = null, ?Comment $comment = null, ?News $news = null, ?Quizz $quizz = null): void
    {
        $notification = new Notification();
        $notification->setType($type)
                     ->setUser($user)
                     ->setReview(
                         $type === 'like' || $type === 'comment' || $type === 'reply' || $type === 'likeComment' || $type === 'review' 
                             ? $review 
                             : null
                     )
                     ->setComment(
                         $type === 'comment' || $type === 'reply' || $type === 'likeComment' 
                             ? $comment 
                             : null
                     )
                     ->setNews(
                         $type === 'newsLike' || $type === 'newsComment' 
                             ? $news 
                             : null
                     )
                     ->setQuizz($type === 'new_quizz'? $quizz : null)  // Ajoute le quizz ici
                     ->setRelatedUser($relatedUser)
                     ->setRead(false);
        
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    public function generateMessage(Notification $notification): string
    {
        switch ($notification->getType()) {
            case 'follow':
                return $notification->getRelatedUser()->getUsername() . ' a commencé à vous suivre.';
            case 'follow_request':
                return $notification->getRelatedUser()->getUsername() . ' a demandé à vous suivre.';
            case 'like':
                return $notification->getRelatedUser()->getUsername() . ' a aimé votre review.';
            case 'comment':
                return $notification->getRelatedUser()->getUsername() . ' a commenté votre post.';
            default:
                return 'Nouvelle notification.';
        }
    }
}