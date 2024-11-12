<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Menu>
 */
class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    // Exemple d'une méthode pour rechercher des menus par nom
    public function findByName(string $name): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.nom LIKE :name')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('m.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Exemple de méthode pour rechercher un menu par son ID
    public function findOneById(int $id): ?Menu
    {
        return $this->find($id);
    }
}
