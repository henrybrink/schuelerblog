<?php

namespace App\Repository;

use App\Entity\AttachedImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AttachedImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method AttachedImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method AttachedImage[]    findAll()
 * @method AttachedImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttachedImageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AttachedImage::class);
    }

    // /**
    //  * @return AttachedImage[] Returns an array of AttachedImage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AttachedImage
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
