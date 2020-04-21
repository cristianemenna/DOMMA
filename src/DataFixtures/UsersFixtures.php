<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UsersFixtures extends Fixture implements OrderedFixtureInterface
{
    private $encode;

    public function __construct(UserPasswordEncoderInterface $encoder) {
        $this->encode = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $encoder = $this->encode;
        $admin = new Users();
        $admin->setPassword(
            $encoder->encodePassword(
                $admin,
                'test'
            )
        );
        $admin->setEmail('admintest@yopmail.com');
        $admin->setUsername('admin');
        $admin->setFirstName('Admin');
        $admin->setLastName('Admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setAttempts(0);

        $userOne = new Users();
        $userOne->setPassword(
            $encoder->encodePassword(
                $userOne,
                'test'
            )
        );
        $userOne->setEmail('usertest@yopmail.com');
        $userOne->setUsername('user1');
        $userOne->setFirstName('User1');
        $userOne->setLastName('User');
        $userOne->setRoles(['ROLE_USER']);
        $userOne->setAttempts(0);

        $userTwo = new Users();
        $userTwo->setPassword(
            $encoder->encodePassword(
                $userTwo,
                'test'
            )
        );

        $userTwo->setEmail('user2test@yopmail.com');
        $userTwo->setUsername('user2');
        $userTwo->setFirstName('User2');
        $userTwo->setLastName('User');
        $userTwo->setRoles(['ROLE_USER']);
        $userTwo->setAttempts(0);

        $manager->persist($admin);
        $manager->persist($userOne);
        $manager->persist($userTwo);
        $manager->flush();

        $this->addReference('user', $userOne);
    }

    public function getOrder()
    {
        return 1;
    }
}
