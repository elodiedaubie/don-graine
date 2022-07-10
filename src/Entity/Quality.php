<?php

namespace App\Entity;

use App\Repository\QualityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QualityRepository::class)]
class Quality
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 30)]
    private $name;

    #[ORM\OneToMany(mappedBy: 'quality', targetEntity: SeedBatch::class)]
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
            $seedBatch->setQuality($this);
        }

        return $this;
    }

    public function removeSeedBatch(SeedBatch $seedBatch): self
    {
        if ($this->seedBatches->removeElement($seedBatch)) {
            // set the owning side to null (unless already changed)
            if ($seedBatch->getQuality() === $this) {
                $seedBatch->setQuality(null);
            }
        }

        return $this;
    }
}
