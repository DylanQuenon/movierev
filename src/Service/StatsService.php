<?php 

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class  StatsService{

    public function __construct(private EntityManagerInterface $manager)
    {}

    /**
     * Permet de récup le nombre d'utilisateur enregistré sur mon site
     *
     * @return integer|null
     */
    public function getUsersCount(): ?int
    {
        return $this->manager->createQuery("SELECT COUNT(u) FROM App\Entity\User u")->getSingleScalarResult();
    }

    /**
     * Permet de récup le nombre de medias
     *
     * @return integer|null
     */
    public function getMediaCount(): ?int
    {
        return $this->manager->createQuery("SELECT COUNT(a) FROM App\Entity\Media a")->getSingleScalarResult();
    }
    /**
     * Permet de récup le nombre d'actus
     *
     * @return integer|null
     */
    public function getNewsCount(): ?int
    {
        return $this->manager->createQuery("SELECT COUNT(a) FROM App\Entity\News a")->getSingleScalarResult();
    }

    /**
     * Permet de récup le nombre de reviews
     *
     * @return integer|null
     */
    public function getReviewsCount(): ?int
    {
        return $this->manager->createQuery("SELECT COUNT(a) FROM App\Entity\Review a")->getSingleScalarResult();
    }
    /**
     * récupère le nombre de likes
     *
     * @return integer|null
     */
    public function getLikesCount(): ?int
    {
        return $this->manager->createQuery("SELECT COUNT(a) FROM App\Entity\Likes a")->getSingleScalarResult();
    }




}

