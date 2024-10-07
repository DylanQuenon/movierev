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


    public function getFollowsNotifications($user)
    {
        return $this->createQueryBuilder('n')
        ->where('n.relatedUser = :user')
        ->andWhere('n.type IN (:types)')
        ->setParameter('user', $user)
        ->setParameter('types', ['follow', 'request'])
        ->orderBy('n.id', 'DESC')
        ->getQuery()
        ->getResult();
    }

    public function getCommentsNotifications($user)
    {
        return $this->createQueryBuilder('n')
        ->where('n.relatedUser = :user')
        ->andWhere('n.type IN (:types)')
        ->setParameter('user', $user)
        ->setParameter('types', ['comment', 'reply'])
        ->orderBy('n.id', 'DESC')
        ->getQuery()
        ->getResult();
    }

    public function countUnreadFollowsNotifications($user)
    {
        return $this->createQueryBuilder('n')
        ->select('COUNT(n.id)')
        ->where('n.relatedUser = :user')
        ->andWhere('n.type IN (:types)')
        ->andWhere('n.isRead = false')
        ->setParameter('user', $user)
        ->setParameter('types', ['follow', 'request'])
        ->getQuery()
        ->getSingleScalarResult();
    }

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


    public function countUnreadCommentsNotifications($user)
    {
        return $this->createQueryBuilder('n')
        ->select('COUNT(n.id)')
        ->where('n.relatedUser = :user')
        ->andWhere('n.type IN (:types)')
        ->andWhere('n.isRead = false')
        ->setParameter('user', $user)
        ->setParameter('types', ['comment', 'reply'])
        ->getQuery()
        ->getSingleScalarResult();
    }


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



    
}
