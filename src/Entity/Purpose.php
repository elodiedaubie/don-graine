<?php

namespace App\Entity;

use App\Repository\PurposeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PurposeRepository::class)]
class Purpose
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 30)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $picto;

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

    public function getPicto(): ?string
    {
        return $this->picto;
    }

    public function setPicto(string $picto): self
    {
        $this->picto = $picto;

        return $this;
    }
}
