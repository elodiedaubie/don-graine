<?php

namespace App\Entity;

use App\Repository\SeedBatchRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeedBatchRepository::class)]
class SeedBatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $seedQuantity;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'seedBatches')]
    #[ORM\JoinColumn(nullable: false)]
    private $owner;

    #[ORM\ManyToOne(targetEntity: Plant::class, inversedBy: 'seedBatches')]
    #[ORM\JoinColumn(nullable: false)]
    private $plant;

    #[ORM\ManyToOne(targetEntity: Quality::class, inversedBy: 'seedBatches')]
    #[ORM\JoinColumn(nullable: false)]
    private $quality;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeedQuantity(): ?int
    {
        return $this->seedQuantity;
    }

    public function setSeedQuantity(int $seedQuantity): self
    {
        $this->seedQuantity = $seedQuantity;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getPlant(): ?Plant
    {
        return $this->plant;
    }

    public function setPlant(?Plant $plant): self
    {
        $this->plant = $plant;

        return $this;
    }

    public function getQuality(): ?Quality
    {
        return $this->quality;
    }

    public function setQuality(?Quality $quality): self
    {
        $this->quality = $quality;

        return $this;
    }
}
