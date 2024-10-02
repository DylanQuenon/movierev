<?php

namespace App\Entity;

use App\Repository\CollectionsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CollectionsRepository::class)]
class Collections
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $isPrivate = null;

    #[ORM\ManyToOne(inversedBy: 'collections')]
    private ?User $user = null;

    /**
     * @var Collection<int, CollectionsMedia>
     */
    #[ORM\OneToMany(targetEntity: CollectionsMedia::class, mappedBy: 'collection')]
    private Collection $collectionsMedia;

    public function __construct()
    {
        $this->collectionsMedia = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getIsPrivate(): ?bool
    {
        return $this->isPrivate;
    }

    public function setIsPrivate(bool $isPrivate): static
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, CollectionsMedia>
     */
    public function getCollectionsMedia(): Collection
    {
        return $this->collectionsMedia;
    }

    public function addCollectionsMedia(CollectionsMedia $collectionsMedium): static
    {
        if (!$this->collectionsMedia->contains($collectionsMedium)) {
            $this->collectionsMedia->add($collectionsMedium);
            $collectionsMedium->setCollection($this);
        }

        return $this;
    }

    public function removeCollectionsMedia(CollectionsMedia $collectionsMedium): static
    {
        if ($this->collectionsMedia->removeElement($collectionsMedium)) {
            // set the owning side to null (unless already changed)
            if ($collectionsMedium->getCollection() === $this) {
                $collectionsMedium->setCollection(null);
            }
        }

        return $this;
    }


}
