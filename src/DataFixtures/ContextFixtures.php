<?php


namespace App\DataFixtures;


use App\Repository\ContextRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Proxies\__CG__\App\Entity\Context;

class ContextFixtures extends Fixture implements OrderedFixtureInterface
{
    private $contextRepository;

    public function __construct(ContextRepository $contextRepository)
    {
        $this->contextRepository = $contextRepository;
    }

    public function load(ObjectManager $manager)
    {
        $context = new Context();
        $context->setTitle('Contexte d\'essai');
        $context->setDescription('Ceci est la description de ce contexte');
        $context->setDuration(10);
        $context->setCreatedAt(new \DateTime('now'));
        $this->contextRepository->createSchema($context->getTitle());

        $user = $this->getReference('user');
        $user->addContext($context);

        $manager->persist($context);
        $manager->persist($user);
        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
    }
}