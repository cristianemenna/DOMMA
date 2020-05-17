<?php

namespace App\Repository;

use App\Entity\Macro;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Macro|null find($id, $lockMode = null, $lockVersion = null)
 * @method Macro|null findOneBy(array $criteria, array $orderBy = null)
 * @method Macro[]    findAll()
 * @method Macro[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MacroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Macro::class);
    }
}
