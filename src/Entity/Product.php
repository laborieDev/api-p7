<?php

namespace App\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="float")
     */
    private $unitPrice;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="products")
     */
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * @return Int $id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return String $name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param String $name
     * @return Product this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Float $unitPrice
     */
    public function getUnitPrice(): ?float
    {
        return $this->unitPrice;
    }

    /**
     * @param Float $unitPrice
     * @return Product this
     */
    public function setUnitPrice(float $unitPrice): self
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param User $user
     * @return Product this
     */
    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addProduct($this);
        }

        return $this;
    }

    /**
     * @param User $user
     * @return Product this
     */
    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeProduct($this);
        }

        return $this;
    }
}
