<?php

namespace App\Repository;

use App\Entity\Context;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Context|null find($id, $lockMode = null, $lockVersion = null)
 * @method Context|null findOneBy(array $criteria, array $orderBy = null)
 * @method Context[]    findAll()
 * @method Context[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContextRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry){
        parent::__construct($registry, Context::class);
    }

    /**
     * Récupère le nom du contexte de travail
     * e crée un schema avec le même nom + l'id du contexte de travail
     *
     * @param Context $context
     * @throws \Exception
     */
    public function createSchema(Context $context)
    {
        $dataBase = $this->getEntityManager()->getConnection();
        $schemaName = $dataBase->quoteIdentifier($context->getTitle() . '_' . $context->getId());

        try {
            $dataBase->prepare('CREATE SCHEMA ' . $schemaName . ' AUTHORIZATION CURRENT_USER')
                ->execute()
                ;
        } catch (\Exception $e) {
            if (strstr($e->getMessage(), 'SQLSTATE[42P06]')) {
               throw new \Exception('Le contexte de travail n\'as pas pu être créé. Veuillez saisir un nouveau titre.');
            }
        }
    }

    /**
     * Récupère l'ancien nom du contexte de travail pour retrouver le nom du schéma en BDD
     * et modifie le nom du schéma avec le nouveau nom du contexte.
     *
     * @param Context $context
     * @param string $contextName
     * @throws \Exception
     */
    public function renameSchema(Context $context, string $contextName)
    {
        $dataBase = $this->getEntityManager()->getConnection();
        $contextNewName = $dataBase->quoteIdentifier($context->getTitle() . '_' . $context->getId());
        $contextOldName = $dataBase->quoteIdentifier($contextName . '_' . $context->getId());

        try {
            $dataBase->prepare('ALTER SCHEMA ' . $contextOldName . ' RENAME TO ' . $contextNewName)
                ->execute()
                ;
        } catch (\Exception $e) {
            if (strstr($e->getMessage(), 'SQLSTATE[42P06]')) {
                throw new \Exception('Le contexte de travail n\'as pas pu être créé. Veuillez saisir un nouveau titre.');
            }
        }

    }

}
