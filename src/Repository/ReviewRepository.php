<?php

namespace App\Repository;

use App\Entity\Media;
use App\Entity\Review;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    //    /**
    //     * @return Review[] Returns an array of Review objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Review
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findReviewsByMediaTitle(string $title)
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.Media', 'm') // Assurez-vous que 'media' est le bon nom de la propriété dans Review
            ->addSelect('m')
            ->where('m.title LIKE :title')
            ->setParameter('title', '%' . $title . '%') // Utilisation d'un LIKE pour une recherche partielle
            ->getQuery()
            ->getResult();
    }

    public function findMostLikedReviews(Media $media)
    {
        // Récupérer les reviews associées au média
        $reviews = $this->createQueryBuilder('r')
            ->where('r.Media = :media')
            ->setParameter('media', $media)
            ->getQuery()
            ->getResult();
    
        // Trier les reviews par le nombre de likes en utilisant la méthode getLikes()
        usort($reviews, function ($a, $b) {
            return $b->getLikes() - $a->getLikes();
        });
    
        // Retourner les 3 reviews avec le plus de likes
        return array_slice($reviews, 0, 3);
    }



    
}
