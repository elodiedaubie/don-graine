<?php

namespace App\Entity;

use App\Repository\PlantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlantRepository::class)]
class Plant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\ManyToOne(targetEntity: Purpose::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $purpose;

    #[ORM\OneToMany(mappedBy: 'plant', targetEntity: SeedBatch::class)]
    private $seedBatches;

    public function __construct()
    {
        $this->seedBatches = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPurpose(): ?Purpose
    {
        return $this->purpose;
    }

    public function setPurpose(?Purpose $purpose): self
    {
        $this->purpose = $purpose;

        return $this;
    }

    /**
     * @return Collection<int, SeedBatch>
     */
    public function getSeedBatches(): Collection
    {
        return $this->seedBatches;
    }

    public function addSeedBatch(SeedBatch $seedBatch): self
    {
        if (!$this->seedBatches->contains($seedBatch)) {
            $this->seedBatches[] = $seedBatch;
            $seedBatch->setPlant($this);
        }

        return $this;
    }

    public function removeSeedBatch(SeedBatch $seedBatch): self
    {
        if ($this->seedBatches->removeElement($seedBatch)) {
            // set the owning side to null (unless already changed)
            if ($seedBatch->getPlant() === $this) {
                $seedBatch->setPlant(null);
            }
        }

        return $this;
    }
}
