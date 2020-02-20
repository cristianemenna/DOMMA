<?php

namespace App\Repository;

use App\Entity\Macros;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Macros|null find($id, $lockMode = null, $lockVersion = null)
 * @method Macros|null findOneBy(array $criteria, array $orderBy = null)
 * @method Macros[]    findAll()
 * @method Macros[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MacrosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Macros::class);
    }

    // /**
    //  * @return Macros[] Returns an array of Macros objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Macros
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
