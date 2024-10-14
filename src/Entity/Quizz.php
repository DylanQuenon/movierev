<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\QuizzRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuizzRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Quizz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre du quizz est obligatoire")]
    #[Assert\Length(max: 255, maxMessage: "Le titre du quizz ne peut pas dépasser 255 caractères")]
    private ?string $title = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $endDate = null;

    /**
     * @var Collection<int, Question>
     */
    #[ORM\OneToMany(targetEntity: Question::class, mappedBy: 'quizz', orphanRemoval: true)]
    private Collection $questions;

    /**
     * @var Collection<int, UserScore>
     */
    #[ORM\OneToMany(targetEntity: UserScore::class, mappedBy: 'quizz', orphanRemoval: true)]
    private Collection $userScores;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'quizz')]
    private Collection $notifications;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->userScores = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    /**
      * Initialise la date de création et la date de fin
      *
      * @return void
      */
    #[ORM\PrePersist]
    public function prePersist(): void
    {
        if(empty($this->createdAt))
        {
            $this->createdAt = new \DateTimeImmutable();
        }
        
        if(empty($this->endDate))
        {
            $this->endDate = $this->createdAt->modify('+1 week');
        }
        
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setQuizz($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): static
    {
        if ($this->questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getQuizz() === $this) {
                $question->setQuizz(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserScore>
     */
    public function getUserScores(): Collection
    {
        return $this->userScores;
    }

    public function addUserScore(UserScore $userScore): static
    {
        if (!$this->userScores->contains($userScore)) {
            $this->userScores->add($userScore);
            $userScore->setQuizz($this);
        }

        return $this;
    }

    public function removeUserScore(UserScore $userScore): static
    {
        if ($this->userScores->removeElement($userScore)) {
            // set the owning side to null (unless already changed)
            if ($userScore->getQuizz() === $this) {
                $userScore->setQuizz(null);
            }
        }

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
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setQuizz($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getQuizz() === $this) {
                $notification->setQuizz(null);
            }
        }

        return $this;
    }
}
