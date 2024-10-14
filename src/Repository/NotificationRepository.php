<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    //    /**
    //     * @return Notification[] Returns an array of Notification objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('n.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Notification
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Récupère toutes les notifs
     *
     * @param [type] $user
     * @param [type] $limit
     * @return void
     */
    public function getAllNotifications($user, $limit = null)
    {
        $qb = $this->createQueryBuilder('n')
            ->where('n.relatedUser = :user')
            ->setParameter('user', $user)
            ->orderBy('n.id', 'DESC');
    
        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }
    
        return $qb->getQuery()->getResult();
    }
    /**
     * 
     *
     * @param array $reviews
     * @param array $comments
     * @return void
     */
    public function getLikesNotifications(array $reviews, array $comments)
    {
        return $this->createQueryBuilder('n')
            ->where('n.review IN (:reviews) AND n.type = :likeType')
            ->orWhere('n.comment IN (:comments) AND n.type = :commentType')
            ->setParameter('reviews', $reviews)
            ->setParameter('comments', $comments)
            ->setParameter('likeType', 'like')
            ->setParameter('commentType', 'likeComment')
            ->orderBy('n.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * récupère les notifs de reviews
     *
     * @param [type] $user
     * @return void
     */
    public function getReviewsNotifications($user)
    {
        return $this->createQueryBuilder('n')
            ->where('n.relatedUser = :user')
            ->andWhere('n.type = :type')
            ->setParameter('user', $user)
            ->setParameter('type', 'review')
            ->orderBy('n.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les notifs de quizz
     *
     * @param [type] $user
     * @return void
     */
    public function getQuizzNotifications($user)
    {
        return $this->createQueryBuilder('n')
        ->where('n.relatedUser = :user')
        ->andWhere('n.type IN (:type)')
        ->setParameter('user', $user)
        ->setParameter('type', 'new_quizz')
        ->orderBy('n.id', 'DESC')
        ->getQuery()
        ->getResult();
    }


    /**
     * récupère les notifs
     *
     * @param [type] $user
     * @return void
     */
    public function getFollowsNotifications($user)
    {
        return $this->createQueryBuilder('n')
        ->where('n.relatedUser = :user')
        ->andWhere('n.type IN (:types)')
        ->setParameter('user', $user)
        ->setParameter('types', ['follow', 'follow_request'])
        ->orderBy('n.id', 'DESC')
        ->getQuery()
        ->getResult();
    }

    /**
     * récupère les commentaires
     *
     * @param [type] $user
     * @return void
     */
    public function getCommentsNotifications($user)
    {
        return $this->createQueryBuilder('n')
        ->where('n.relatedUser = :user')
        ->andWhere('n.type IN (:types)')
        ->setParameter('user', $user)
        ->setParameter('types', ['comment', 'reply','newsComment'])
        ->orderBy('n.id', 'DESC')
        ->getQuery()
        ->getResult();
    }

    /**
     * Undocumented function
     *
     * @param [type] $user
     * @return void
     */
    public function countUnreadFollowsNotifications($user)
    {
        return $this->createQueryBuilder('n')
        ->select('COUNT(n.id)')
        ->where('n.relatedUser = :user')
        ->andWhere('n.type IN (:types)')
        ->andWhere('n.isRead = false')
        ->setParameter('user', $user)
        ->setParameter('types', ['follow', 'follow_request'])
        ->getQuery()
        ->getSingleScalarResult();
    }

    public function countUnreadQuizzNotifications($user)
    {
        return $this->createQueryBuilder('n')
        ->select('COUNT(n.id)')
        ->where('n.relatedUser = :user')
        ->andWhere('n.type IN (:types)')
        ->andWhere('n.isRead = false')
        ->setParameter('user', $user)
        ->setParameter('types', ['new_quizz'])
        ->getQuery()
        ->getSingleScalarResult();
    }

    /**
     * Compte les notifs des reviews non lues
     *
     * @param [type] $user
     * @return void
     */
    public function countUnreadReviewsNotifications($user)
    {
        return $this->createQueryBuilder('n')
        ->select('COUNT(n.id)')
        ->where('n.relatedUser = :user')
        ->andWhere('n.type IN (:types)')
        ->andWhere('n.isRead = false')
        ->setParameter('user', $user)
        ->setParameter('types', ['review'])
        ->getQuery()
        ->getSingleScalarResult();

    }

    /**
     * Compte les notifs des commentaires
     *
     * @param [type] $user
     * @return void
     */
    public function countUnreadCommentsNotifications($user)
    {
        return $this->createQueryBuilder('n')
        ->select('COUNT(n.id)')
        ->where('n.relatedUser = :user')
        ->andWhere('n.type IN (:types)')
        ->andWhere('n.isRead = false')
        ->setParameter('user', $user)
        ->setParameter('types', ['comment', 'reply','newsComment'])
        ->getQuery()
        ->getSingleScalarResult();
    }

    /**
     * Compte les notifs pas lues
     *
     * @param [type] $user
     * @return void
     */
    public function countUnreadNotifications($user)
    {
        return $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.relatedUser = :user')
            ->andWhere('n.isRead = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
    /**
     * Compte les notifs non lues pr les likes
     *
     * @param [type] $user
     * @param array $reviews
     * @param array $comments
     * @return void
     */
    public function countUnreadLikesNotifications($user, array $reviews, array $comments)
    {
        return $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where(
                'n.review IN (:reviews) AND n.type = :likeType'
            )
            ->orWhere(
                'n.comment IN (:comments) AND n.type = :commentType'
            )
            ->andWhere('n.isRead = false')
            ->setParameter('reviews', $reviews)
            ->setParameter('comments', $comments)
            ->setParameter('likeType', 'like')
            ->setParameter('commentType', 'likeComment')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * marquer ls likes comme lu
     *
     * @param [type] $user
     * @return void
     */
    public function markLikesAsRead($user): void
    {
        $this->createQueryBuilder('n')
            ->update()
            ->set('n.isRead', 'true')
            ->where(
                'n.relatedUser = :user AND (n.type = :likeType OR n.type = :commentType)'
            )
            ->setParameter('user', $user)
            ->setParameter('likeType', 'like')
            ->setParameter('commentType', 'likeComment')
            ->getQuery()
            ->execute();
    }    

    /**
     * Marquer les follows comme lues
     *
     * @param [type] $user
     * @return void
     */
    public function markFollowsAsRead($user)
    {
        $this->createQueryBuilder('n')
        ->update()
        ->set('n.isRead', ':isRead')
        ->where('n.relatedUser = :user')
        ->andWhere('n.type IN (:types)')
        ->setParameter('user', $user)
        ->setParameter('types', ['follow', 'request'])
        ->setParameter('isRead', true)
        ->getQuery()
        ->execute();
    }
    /**
     * Marque les reviews comme lu
     *
     * @param [type] $user
     * @return void
     */
    public function markReviewsAsRead($user)
    {
        $this->createQueryBuilder('n')
        ->update()
        ->set('n.isRead', ':isRead')
        ->where('n.relatedUser = :user')
        ->andWhere('n.type IN (:types)')
        ->setParameter('user', $user)
        ->setParameter('types', ['review'])
        ->setParameter('isRead', true)
        ->getQuery()
        ->execute();
    }

    public function markQuizzAsRead($user)
    {
        $this->createQueryBuilder('n')
        ->update()
        ->set('n.isRead', ':isRead')
        ->where('n.relatedUser = :user')
        ->andWhere('n.type IN (:types)')
        ->setParameter('user', $user)
        ->setParameter('types', ['new_quizz'])
        ->setParameter('isRead', true)
        ->getQuery()
        ->execute();
    }

    /**
     * marquer les commentaires comme lues
     *
     * @param [type] $user
     * @return void
     */
    public function markCommentsAsRead($user)
    {
        $this->createQueryBuilder('n')
            ->update()
            ->set('n.isRead', ':isRead')
            ->where('n.relatedUser = :user')
            ->andWhere('n.type IN (:types)')
            ->setParameter('user', $user)
            ->setParameter('types', ['comment', 'reply'])
            ->setParameter('isRead', true)
            ->getQuery()
            ->execute();
    }

    /**
     * Marquer toutes les notifs comme
     *
     * @param [type] $user
     * @return void
     */
    public function markAllNotificationsAsRead($user)
    {
        $this->createQueryBuilder('n')
            ->update()
            ->set('n.isRead', ':isRead')
            ->where('n.relatedUser = :user')
            ->setParameter('user', $user)
            ->setParameter('isRead', true)
            ->getQuery()
            ->execute();
    }



    
}
