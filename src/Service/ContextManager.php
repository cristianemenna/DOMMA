<?php


namespace App\Service;


use App\Entity\Context;
use Doctrine\ORM\EntityManagerInterface;

class ContextManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

    }

    /**
     * Lors de la suppression d'un utilisateur :
     * Itère sur ses contextes de travail,
     * s'il est le seul à avoir accès les supprime aussi, ainsi que le schema correspondant.
     *
     * @param array $contexts
     * @throws \Doctrine\DBAL\DBALException
     */
    public function removeContextsFromUser($contexts)
    {
        foreach ($contexts as $context) {
            if (count($context->getUsers()) === 1) {
                $this->removeContext($context);
            }
        }
    }

    /**
     * Supprime un contexte de travail, son schema et imports associés
     *
     * @param Context $context
     * @throws \Doctrine\DBAL\DBALException
     */
    public function removeContext(Context $context)
    {
        $this->removeSchema($context->getTitle());
        $this->entityManager->remove($context);
        $this->entityManager->flush();
    }

    /**
     * Supprime un schema et ses imports associés
     *
     * @param string $contextName
     * @throws \Doctrine\DBAL\DBALException
     */
    private function removeSchema(string $contextName)
    {
        $dataBase = $this->entityManager->getConnection();
        $schema = $dataBase->quoteIdentifier(mb_strtolower($contextName));

        $dataBase->prepare('DROP SCHEMA ' . $schema . ' CASCADE')
            ->execute()
            ;
    }

}