<?php

namespace App\Entity;

use App\Repository\FollowRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FollowRequestRepository::class)]
class FollowRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'followRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $requester = null;

    #[ORM\ManyToOne(inversedBy: 'followRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $requested = null;

    #[ORM\Column]
    private ?bool $is_accepted = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequester(): ?User
    {
        return $this->requester;
    }

    public function setRequester(?User $requester): static
    {
        $this->requester = $requester;

        return $this;
    }

    public function getRequested(): ?User
    {
        return $this->requested;
    }

    public function setRequested(?User $requested): static
    {
        $this->requested = $requested;

        return $this;
    }

    public function isAccepted(): ?bool
    {
        return $this->is_accepted;
    }

    public function setAccepted(bool $is_accepted): static
    {
        $this->is_accepted = $is_accepted;

        return $this;
    }
}
