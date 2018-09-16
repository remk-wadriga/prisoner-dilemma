<?php

namespace App\DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;

class UserFixture extends AbstractFixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(User::class, 5, function (User $user, int $i) {
            switch ($i) {
                case 0:
                    // 1. Create default admin
                    $user
                        ->setEmail('remkwadriga2013@gmail.com')
                        ->setFirstName('Rem')
                        ->setLastName('Kwadriga')
                        ->setPassword($this->encoder->encodePassword($user, 'test'))
                        ->setRoles(['ROLE_ADMIN']);
                    break;
                case 1:
                    // Create default owner
                    $user
                        ->setEmail('owner@gmail.com')
                        ->setFirstName('Default')
                        ->setLastName('Owner')
                        ->setPassword($this->encoder->encodePassword($user, 'test'))
                        ->setRoles(['ROLE_OWNER']);
                    break;
                case 2:
                    // Create default user
                    $user
                        ->setEmail('user@gmail.com')
                        ->setFirstName('Default')
                        ->setLastName('User')
                        ->setPassword($this->encoder->encodePassword($user, 'test'))
                        ->setRoles(['ROLE_USER']);
                    break;
                default:
                    // Create random user
                    $user
                        ->setEmail($this->faker->email)
                        ->setFirstName($this->faker->firstName)
                        ->setLastName($this->faker->lastName)
                        ->setPassword($this->encoder->encodePassword($user, 'test'))
                        ->setRoles(['ROLE_USER']);
                    break;
            }
        });

        $manager->flush();
    }
}
