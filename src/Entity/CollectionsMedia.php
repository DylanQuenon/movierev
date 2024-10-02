<?php

namespace App\Entity;

use App\Repository\CollectionsMediaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CollectionsMediaRepository::class)]
class CollectionsMedia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'collectionsMedia')]
    private ?Collections $collection = null;

    #[ORM\ManyToOne(inversedBy: 'collectionsMedia')]
    private ?Media $medias = null;

    #[ORM\Column]
    private ?int $position = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCollection(): ?Collections
    {
        return $this->collection;
    }

    public function setCollection(?Collections $collection): static
    {
        $this->collection = $collection;

        return $this;
    }

    public function getMedias(): ?Media
    {
        return $this->medias;
    }

    public function setMedias(?Media $medias): static
    {
        $this->medias = $medias;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }
}
