<?php

namespace App\Entity;

use App\Repository\SeedBatchRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SeedBatchRepository::class)]
class SeedBatch
{
    //to update min/max quantities, just update thoses constants, use everywhere even in fixtures
    public const MINSEEDS = 5;
    public const MAXSEEDS = 25;
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
        value: self::MAXSEEDS,
        message:'La quantité de graines est trop élevée'
    )]
    #[Assert\GreaterThanOrEqual(
        value: self::MINSEEDS,
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

    #[ORM\OneToMany(mappedBy: 'seedBatch', targetEntity: Donation::class)]
    private Collection $donations;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'favoriteList')]
    private Collection $favoriteOwners;

    public function __construct()
    {
        $this->donations = new ArrayCollection();
        $this->favoriteOwners = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Donation>
     */
    public function getDonations(): Collection
    {
        return $this->donations;
    }

    public function addDonation(Donation $donation): self
    {
        if (!$this->donations->contains($donation)) {
            $this->donations[] = $donation;
            $donation->setSeedBatch($this);
        }

        return $this;
    }

    public function removeDonation(Donation $donation): self
    {
        if ($this->donations->removeElement($donation)) {
            // set the owning side to null (unless already changed)
            if ($donation->getSeedBatch() === $this) {
                $donation->setSeedBatch(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getFavoriteOwners(): Collection
    {
        return $this->favoriteOwners;
    }

    public function addFavoriteOwner(User $favoriteOwner): self
    {
        if (!$this->favoriteOwners->contains($favoriteOwner)) {
            $this->favoriteOwners[] = $favoriteOwner;
        }

        return $this;
    }

    public function removeFavoriteOwner(User $favoriteOwner): self
    {
        $this->favoriteOwners->removeElement($favoriteOwner);

        return $this;
    }

    //if there is a least one going donation or canceled for this batch , return false
    //if there is only canceled donations, return true
    public function isAvailable(): bool
    {
        foreach ($this->getDonations() as $donation) {
            if (
                $donation->getStatus() === Donation::STATUS[0]
                || $donation->getStatus() === Donation::STATUS[1]
            ) {
                //there is already an active donation for this batch
                return false;
            }
        }
        return true;
    }
}
