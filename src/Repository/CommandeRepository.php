<?php

namespace App\Repository;

use App\Entity\Commande;
use App\Entity\Client; // Ajoute l'importation de Client
use App\Entity\Restaurant; // Ajoute l'importation de Restaurant
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    // Exemple de méthode pour rechercher des commandes par client
    public function findByClient(Client $client): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.client = :client')
            ->setParameter('client', $client)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Exemple de méthode pour rechercher des commandes par restaurant
    public function findByRestaurant(Restaurant $restaurant): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}



