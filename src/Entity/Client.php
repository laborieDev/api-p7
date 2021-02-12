<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ClientRepository::class)
 */
class Client
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="clients", cascade={"persist"})
     */
    private $user;

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
     * @return Client this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return User $user
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Client this
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
