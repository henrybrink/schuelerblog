<?php

namespace App\Repository;

use App\Entity\IServUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method IServUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method IServUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method IServUser[]    findAll()
 * @method IServUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IServUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, IServUser::class);
    }

    // /**
    //  * @return IServUser[] Returns an array of IServUser objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?IServUser
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
