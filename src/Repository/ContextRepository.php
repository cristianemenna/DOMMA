<?php

namespace App\Repository;

use App\Entity\Context;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * @method Context|null find($id, $lockMode = null, $lockVersion = null)
 * @method Context|null findOneBy(array $criteria, array $orderBy = null)
 * @method Context[]    findAll()
 * @method Context[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContextRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Context::class);
    }

    /**
     * @param string $contextName
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */

    // Récuperer le nom du contexte de travail et substitue les espaces par '_'
    // Crée un schema avec le même nom modifié
    public function createSchema(string $contextName)
    {
        $dataBase = $this->getEntityManager()->getConnection();
        $contextNameNoSpaces = str_replace([' ', '(', ')', '/', '-', ',', '\'', '*', '+', '&', '#', '"', '.', '!', ':', '?', '='],
            '_', $contextName);
        return $dataBase->prepare('CREATE SCHEMA ' . $contextNameNoSpaces . ' AUTHORIZATION CURRENT_USER')
            ->execute()
            ;
    }

    // /**
    //  * @return Context[] Returns an array of Context objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Context
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
