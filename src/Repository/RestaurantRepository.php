<?php

namespace App\Repository;

use App\Entity\Restaurant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Restaurant>
 */
class RestaurantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Restaurant::class);
    }

    // Exemple d'une méthode pour rechercher un restaurant par nom
    public function findByName(string $name): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.nom LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('r.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Exemple d'une méthode pour récupérer un restaurant par son ID
    public function findOneById(int $id): ?Restaurant
    {
        return $this->find($id);
    }
}
