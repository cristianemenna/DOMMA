<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UsersFixtures extends Fixture
{
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

        $manager->persist($admin);
        $manager->flush();
    }
}
