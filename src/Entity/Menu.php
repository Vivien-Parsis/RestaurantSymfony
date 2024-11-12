<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $nom = null;

    // Relation OneToMany avec l'entité Plats
    #[ORM\OneToMany(mappedBy: 'menu', targetEntity: Plats::class)]
    private Collection $listePlat;

    public function __construct()
    {
        $this->listePlat = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    // Getter pour la collection de Plats
    public function getListePlat(): Collection
    {
        return $this->listePlat;
    }

    // Méthode pour ajouter un Plat à la collection
    public function addPlat(Plats $plat): static
    {
        if (!$this->listePlat->contains($plat)) {
            $this->listePlat[] = $plat;
            $plat->setMenu($this);  // Assure que la relation inverse est bien définie
        }
        return $this;
    }

    // Méthode pour retirer un Plat de la collection
    public function removePlat(Plats $plat): static
    {
        if ($this->listePlat->contains($plat)) {
            $this->listePlat->removeElement($plat);
            // On peut aussi enlever la relation inverse si nécessaire
            // $plat->setMenu(null);
        }
        return $this;
    }
}

