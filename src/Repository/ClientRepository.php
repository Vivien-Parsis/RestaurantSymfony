<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    // Exemple de méthode pour rechercher un client par son nom
    public function findByNom(string $nom): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.nom LIKE :nom')
            ->setParameter('nom', '%' . $nom . '%')
            ->orderBy('c.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Exemple de méthode pour récupérer un client par son ID
    public function findOneById(int $id): ?Client
    {
        return $this->find($id);
    }
}

