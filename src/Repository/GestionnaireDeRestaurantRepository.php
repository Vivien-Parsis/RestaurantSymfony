<?php

namespace App\Repository;

use App\Entity\GestionnaireDeRestaurant;
use App\Entity\Restaurant;  // Ajoute cette ligne pour importer l'entité Restaurant
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GestionnaireDeRestaurant>
 */
class GestionnaireDeRestaurantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GestionnaireDeRestaurant::class);
    }

    // Exemple de méthode pour rechercher un gestionnaire par restaurant
    public function findByRestaurant(Restaurant $restaurant): ?GestionnaireDeRestaurant
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
