<?php

namespace App\Entity;

use DateTimeImmutable;
use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\NewsRepository;

#[ORM\Entity(repositoryClass: NewsRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields:['title'], message:"Un autre media possède déjà ce titre, merci de le modifier")]
class News
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 5, max: 255, minMessage:"Le titre doit faire plus de 5 caractères", maxMessage: "Le titre ne doit pas faire plus de 255 caractères")]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Image(mimeTypes:['image/png','image/jpeg', 'image/jpg', 'image/gif', 'image/webp'], mimeTypesMessage:"Vous devez upload un fichier jpg, jpeg, webp, png ou gif")]
    #[Assert\File(maxSize:"2048k", maxSizeMessage: "La taille du fichier est trop grande")]
    private ?string $cover = null;

    #[ORM\ManyToOne(inversedBy: 'news')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\Column(nullable: true)]
    private ?int $viewsCount = null;

    #[ORM\Column(nullable: true)]
    private ?int $shareCount = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        if(empty($this->createdAt))
        {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

        /**
     * Permet de créer un slug automatiquement avec le nom et prénom de l'utilisateur
     *
     * @return void
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function initializeSlug(): void
    {
        if(empty($this->slug))
        {
            $slugify = new Slugify();
            $this->slug = $slugify->slugify($this->title);
        }
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

   
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getCover(): ?string
    {
        return $this->cover;
    }

    public function setCover(string $cover): static
    {
        $this->cover = $cover;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getViewsCount(): ?int
    {
        return $this->viewsCount;
    }

    public function setViewsCount(int $viewsCount): static
    {
        $this->viewsCount = $viewsCount;

        return $this;
    }

    public function getShareCount(): ?int
    {
        return $this->shareCount;
    }

    public function setShareCount(int $shareCount): static
    {
        $this->shareCount = $shareCount;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
