<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    private ?restaurant $Restaurant = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    private ?user $client = null;

    /**
     * @var Collection<int, plat>
     */
    #[ORM\ManyToMany(targetEntity: plat::class, inversedBy: 'commandes')]
    private Collection $plats;

    public function __construct()
    {
        $this->plats = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRestaurant(): ?restaurant
    {
        return $this->Restaurant;
    }

    public function setRestaurant(?restaurant $Restaurant): static
    {
        $this->Restaurant = $Restaurant;

        return $this;
    }

    public function getClient(): ?user
    {
        return $this->client;
    }

    public function setClient(?user $client): static
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection<int, plat>
     */
    public function getPlats(): Collection
    {
        return $this->plats;
    }

    public function addPlat(plat $plat): static
    {
        if (!$this->plats->contains($plat)) {
            $this->plats->add($plat);
        }

        return $this;
    }

    public function removePlat(plat $plat): static
    {
        $this->plats->removeElement($plat);

        return $this;
    }
}