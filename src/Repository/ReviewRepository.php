<?php

namespace App\Repository;

use App\Entity\User;
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

    /**
     * Trouver les reviews associées à un média
     *
     * @param string $title
     * @return void
     */
    public function findReviewsByMediaTitle(string $title)
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.Media', 'm') // Assurez-vous que 'media' est le bon nom de la propriété dans Review
            ->innerJoin('r.author', 'a') // Ajoutez cette ligne pour joindre l'auteur
            ->addSelect('m, a') // Ajoutez 'a' pour sélectionner l'auteur
            ->where('m.title LIKE :title')
            ->andWhere('a.isPrivate = :isPrivate') // Condition pour exclure les auteurs privés
            ->setParameter('title', '%' . $title . '%') // Utilisation d'un LIKE pour une recherche partielle
            ->setParameter('isPrivate', false) // Supposons que false signifie que l'auteur n'est pas privé
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Trouver les reviews les plus likées pour un médoa
     *
     * @param Media $media
     * @return void
     */
    public function findMostLikedReviews(Media $media)
    {
        // Récupérer les reviews associées au média, en excluant les auteurs avec un compte privé
        $reviews = $this->createQueryBuilder('r')
            ->innerJoin('r.author', 'a') // Joindre la table des auteurs
            ->where('r.Media = :media')
            ->andWhere('a.isPrivate = :isPrivate') // Exclure les auteurs avec un compte privé
            ->setParameter('media', $media)
            ->setParameter('isPrivate', false) // Supposons que false signifie que l'auteur n'est pas privé
            ->getQuery()
            ->getResult();
        
        // Trier les reviews par le nombre de likes en utilisant la méthode getLikes()
        usort($reviews, function ($a, $b) {
            return count($b->getLikes()) - count($a->getLikes()); // Comparez le nombre de likes
        });
        
    
        // Retourner les 3 reviews avec le plus de likes
        return array_slice($reviews, 0, 3);
    }
    

    /**
     * -Trouver les reviews publiques
     *
     * @param integer|null $limit
     * @return void
     */
    public function findPublicReviews(?int $limit = null)
    {
        $queryBuilder = $this->createQueryBuilder('r')
            ->innerJoin('r.author', 'u') 
            ->where('u.isPrivate = false') // Filtrer les utilisateurs dont le compte est public
            ->orderBy('r.createdAt', 'DESC'); // Trier par date de création, du plus récent au plus ancien

        if ($limit !== null) {
            $queryBuilder->setMaxResults($limit); // Appliquer la limite si définie
        }

        return $queryBuilder->getQuery()
            ->getResult();
    }

    /**
     * reviews des followers
     *
     * @param User $currentUser
     * @return void
     */
    public function findReviewsFromFollowedUsers(User $currentUser)
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.author', 'u') // Liaison avec l'auteur
            ->innerJoin('u.followeds', 's') // Liaison avec les abonnements (les abonnés de l'auteur)
            ->where('s.follower = :currentUser') // Seules les critiques des utilisateurs suivis par l'utilisateur courant
            ->setParameter('currentUser', $currentUser->getId())
            ->orderBy('r.createdAt', 'DESC') // Trier par date de création, du plus récent au plus ancien
            ->getQuery()
            ->getResult();
    }
    




    
}
