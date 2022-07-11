<?php

namespace App\Entity;

use App\Repository\SeedBatchRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SeedBatchRepository::class)]
class SeedBatch
{
    //to update min/max quantities, just update thoses constants, use everywhere even in fixtures
    public const MINSEEDSQUANTITY = 5;
    public const MAXSEEDSQUANTITY = 25;
    public const MAXBATCHADDED = 10;
    public const MINBATCHADDED = 1;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(
        message: 'Vous devez renseigner une quantité'
    )]
    #[Assert\Type(
        type: 'integer',
        message: '{{ value }} n\'est pas un nombre entier.',
    )]
    #[Assert\LessThanOrEqual(
        value: self::MAXSEEDSQUANTITY,
        message:'La quantité de graines est trop élevée'
    )]
    #[Assert\GreaterThanOrEqual(
        value: self::MINSEEDSQUANTITY,
        message:'La quantité de graines est trop faible'
    )]
    private int $seedQuantity;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'seedBatches')]
    #[ORM\JoinColumn(nullable: false)]
    private User $owner;

    #[ORM\ManyToOne(targetEntity: Plant::class, inversedBy: 'seedBatches')]
    #[ORM\JoinColumn(nullable: false)]
    private Plant $plant;

    #[ORM\ManyToOne(targetEntity: Quality::class, inversedBy: 'seedBatches')]
    #[ORM\JoinColumn(nullable: false)]
    private Quality $quality;

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
