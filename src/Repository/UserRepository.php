<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
    public function findByRoles(array $roles): array
    {
      
        $qb = $this->createQueryBuilder('u');

    
        foreach ($roles as $index => $role) {
            $qb->orWhere('u.roles LIKE :role' . $index)
               ->setParameter('role' . $index, '%' . $role . '%');
        }

        // Return results
        return $qb
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findLikedReviewsByUser(User $user){
        $qb = $this->createQueryBuilder('u');
        $qb->join('u.likes', 'lr')
        ->where('lr.author = :user')
        ->setParameter('user', $user);
        return $qb->getQuery()->getResult();

    }

    
    public function findByUsername(string $term): QueryBuilder
    {
        return $this->createQueryBuilder('a')
            ->where('a.username LIKE :term')
            ->setParameter('term', '%' . $term . '%');
    }




  
  


    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
