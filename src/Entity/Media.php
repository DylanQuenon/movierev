<?php

namespace App\Entity;

use DateTime;
use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MediaRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields:['title'], message:"Un autre media possède déjà ce titre, merci de le modifier")]
class Media
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 5, max: 255, minMessage:"Le titre doit faire plus de 5 caractères", maxMessage: "Le titre ne doit pas faire plus de 255 caractères")]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTime $release_date = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type ne peut pas être vide")]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 2, max: 100, minMessage:"La durée doit faire plus de 2 caractères", maxMessage: "La durée ne doit pas faire plus de 100 caractères")]
    private ?string $duration = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Length(min: 50, minMessage:"Le synopsis doit faire plus de 50 caractères")]
    private ?string $synopsis = null;

    #[ORM\Column(length: 255)]
    #[Assert\Image(mimeTypes:['image/png','image/jpeg', 'image/jpg', 'image/gif', 'image/webp'], mimeTypesMessage:"Vous devez upload un fichier jpg, jpeg, webp, png ou gif")]
    #[Assert\File(maxSize:"1024k", maxSizeMessage: "La taille du fichier est trop grande")]
    private ?string $poster = null;

    #[ORM\Column(length: 255)]
    #[Assert\Image(mimeTypes:['image/png','image/jpeg', 'image/jpg', 'image/gif', 'image/webp'], mimeTypesMessage:"Vous devez upload un fichier jpg, jpeg, webp, png ou gif")]
    #[Assert\File(maxSize:"1024k", maxSizeMessage: "La taille du fichier est trop grande")]
    private ?string $cover = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 2, max: 100, minMessage:"La durée doit faire plus de 2 caractères", maxMessage: "La durée ne doit pas faire plus de 100 caractères")]
    private ?string $producer = null;

    #[ORM\Column(length: 255)]
    private ?string $trailer = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    /**
     * @var Collection<int, Genre>
     */
    #[ORM\ManyToMany(targetEntity: Genre::class, inversedBy: 'media')]
    private Collection $genres;

    /**
     * @var Collection<int, Casting>
     */
    #[ORM\OneToMany(targetEntity: Casting::class, mappedBy: 'media',orphanRemoval: true)]
    private Collection $castings;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'Media', orphanRemoval: true)]
    private Collection $reviews;

    /**
     * @var Collection<int, CollectionsMedia>
     */
    #[ORM\OneToMany(targetEntity: CollectionsMedia::class, mappedBy: 'medias')]
    private Collection $collectionsMedia;



    public function __construct()
    {
        $this->genres = new ArrayCollection();
        $this->castings = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->collectionsMedia = new ArrayCollection();
    }
    /**
     * Moyenne des notes
     *
     * @return integer
     */
    public function getAvgRatings(): int
    {
        // calculer la somme des notations
        // la fonction array_reduce permet de réduire le tableau à une seule valeur 1er param c'est le tableau à reduire et en 2ème paramètre de la fonction c'est la fonction à faire pour chaque valeur, 3ème c'est la valeur par défaut
        $sum = array_reduce($this->reviews->toArray(),function($total,$reviews){
            return $total + $reviews->getRating();
        },0);

        // faire la division pour avoir la moyenne (ternaire)
        if(count($this->reviews) > 0) return $moyenne = round($sum / count($this->reviews));

        return 0;
    }

     /**
     * Permet de créer un slug automatiquement avec le titre du film
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

    public function getReleaseDate(): ?DateTime
    {
        return $this->release_date;
    }

    public function setReleaseDate(\DateTime $release_date): static
    {
        $this->release_date = $release_date;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(string $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(string $synopsis): static
    {
        $this->synopsis = $synopsis;

        return $this;
    }

    public function getPoster(): ?string
    {
        return $this->poster;
    }

    public function setPoster(string $poster): static
    {
        $this->poster = $poster;

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

    public function getProducer(): ?string
    {
        return $this->producer;
    }

    public function setProducer(string $producer): static
    {
        $this->producer = $producer;

        return $this;
    }

    public function getTrailer(): ?string
    {
        return $this->trailer;
    }

    public function setTrailer(string $trailer): static
    {
        $this->trailer = $trailer;

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

    /**
     * @return Collection<int, Genre>
     */
    public function getGenres(): Collection
    {
        return $this->genres;
    }

    public function addGenre(Genre $genre): static
    {
        if (!$this->genres->contains($genre)) {
            $this->genres->add($genre);
        }

        return $this;
    }

    public function removeGenre(Genre $genre): static
    {
        $this->genres->removeElement($genre);

        return $this;
    }

    /**
     * @return Collection<int, Casting>
     */
    public function getCastings(): Collection
    {
        return $this->castings;
    }

    public function addCasting(Casting $casting): static
    {
        if (!$this->castings->contains($casting)) {
            $this->castings->add($casting);
            $casting->setMedia($this);
        }

        return $this;
    }

    public function removeCasting(Casting $casting): static
    {
        if ($this->castings->removeElement($casting)) {
            // set the owning side to null (unless already changed)
            if ($casting->getMedia() === $this) {
                $casting->setMedia(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setMedia($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getMedia() === $this) {
                $review->setMedia(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CollectionsMedia>
     */
    public function getCollectionsMedia(): Collection
    {
        return $this->collectionsMedia;
    }

    public function addCollectionsMedium(CollectionsMedia $collectionsMedium): static
    {
        if (!$this->collectionsMedia->contains($collectionsMedium)) {
            $this->collectionsMedia->add($collectionsMedium);
            $collectionsMedium->setMedias($this);
        }

        return $this;
    }

    public function removeCollectionsMedium(CollectionsMedia $collectionsMedium): static
    {
        if ($this->collectionsMedia->removeElement($collectionsMedium)) {
            // set the owning side to null (unless already changed)
            if ($collectionsMedium->getMedias() === $this) {
                $collectionsMedium->setMedias(null);
            }
        }

        return $this;
    }



}
