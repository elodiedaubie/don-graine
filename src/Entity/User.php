<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]

    private int $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank(
        message: 'Vous devez renseigner une adresse email'
    )]
    #[Assert\Email(
        message: '{{ value }} n\'est pas une adresse email valide.',
    )]
    private string $email;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'string', length: 25)]
    #[Assert\NotBlank(
        message: 'Vous devez renseigner un pseudo'
    )]
    #[Assert\Length(
        min: 2,
        max: 25,
        minMessage: 'Votre pseudo doit comporter au moins {{ limit }} caractères',
        maxMessage: 'Votre pseudo ne peut pas dépasser {{ limit }} caractères',
    )]
    private string $username;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'boolean')]
    private bool $isVerified = false;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: SeedBatch::class, orphanRemoval: true)]
    private Collection $seedBatches;

    #[ORM\OneToMany(mappedBy: 'beneficiary', targetEntity: Donation::class)]
    private Collection $donationsReceived;

    #[ORM\ManyToMany(targetEntity: SeedBatch::class, mappedBy: 'favoriteOwners')]
    private Collection $favoriteList;

    public function __construct()
    {
        $this->seedBatches = new ArrayCollection();
        $this->donationsReceived = new ArrayCollection();
        $this->favoriteList = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

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
            $seedBatch->setOwner($this);
        }

        return $this;
    }

    public function removeSeedBatch(SeedBatch $seedBatch): self
    {
        if ($this->seedBatches->removeElement($seedBatch)) {
            // set the owning side to null (unless already changed)
            if ($seedBatch->getOwner() === $this) {
                $seedBatch->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * Get all donations made for a specific user
     */
    public function getDonationsMade(): array
    {
        $donations = [];

        if (!empty($this->getSeedBatches())) {
            foreach ($this->getSeedBatches() as $userBatch) {
                //get donations made by users for each batch
                if (!empty($userBatch->getDonations())) {
                    foreach ($userBatch->getDonations() as $donation) {
                        $donations [] = $donation;
                    }
                }
            }
        }
        return $donations;
    }

    /**
     * @return Collection<int, Donation>
     */
    public function getDonationsReceived(): Collection
    {
        return $this->donationsReceived;
    }

    public function addDonationsReceived(Donation $donationsReceived): self
    {
        if (!$this->donationsReceived->contains($donationsReceived)) {
            $this->donationsReceived[] = $donationsReceived;
            $donationsReceived->setBeneficiary($this);
        }

        return $this;
    }

    public function removeDonationsReceived(Donation $donationsReceived): self
    {
        if ($this->donationsReceived->removeElement($donationsReceived)) {
            // set the owning side to null (unless already changed)
            if ($donationsReceived->getBeneficiary() === $this) {
                $donationsReceived->setBeneficiary(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SeedBatch>
     */
    public function getFavoriteList(): Collection
    {
        return $this->favoriteList;
    }

    public function addFavoriteList(SeedBatch $favoriteList): self
    {
        if (!$this->favoriteList->contains($favoriteList)) {
            $this->favoriteList[] = $favoriteList;
            $favoriteList->addFavoriteOwner($this);
        }

        return $this;
    }

    public function removeFavoriteList(SeedBatch $favoriteList): self
    {
        if ($this->favoriteList->removeElement($favoriteList)) {
            $favoriteList->removeFavoriteOwner($this);
        }

        return $this;
    }

    public function hasInFavorites(SeedBatch $seedBatch): bool
    {
        if ($this->favoriteList->contains($seedBatch)) {
            return true;
        }
        return false;
    }

     /**
     * get all batches available for a specific User, ordered by ID DESC
     */
    public function getAvailableBatches(): array
    {
        //$userBatches = $this->seedBatchRepository->findByOwner($user, ['id' => 'DESC']);
        $userBatches = $this->getSeedBatches();
        $availableBatches = [];

        if (!empty($userBatches)) {
            foreach ($userBatches as $userBatch) {
                if ($userBatch->isAvailable()) {
                    //get available seed batches only
                    $availableBatches[] = $userBatch;
                }
            }
        }
        return $availableBatches;
    }
}
