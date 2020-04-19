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

        $user = new Users();
        $user->setPassword(
            $encoder->encodePassword(
                $user,
                'test'
            )
        );
        $user->setEmail('usertest@yopmail.com');
        $user->setUsername('user');
        $user->setFirstName('User');
        $user->setLastName('User');
        $user->setRoles(['ROLE_USER']);
        $user->setAttempts(0);

        $manager->persist($admin);
        $manager->persist($user);
        $manager->flush();

        $this->addReference('user', $user);
    }

    public function getOrder()
    {
        return 1;
    }
}
