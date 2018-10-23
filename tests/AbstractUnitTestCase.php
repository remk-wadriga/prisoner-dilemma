<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 23.10.2018
 * Time: 11:36
 */

namespace App\Tests;

use PHPUnit\Framework\IncompleteTestError;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManager;
use Faker\Factory;
use App\Entity\User;


class AbstractUnitTestCase extends KernelTestCase
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var User|null
     */
    protected $user;

    /** @var \Faker\Generator */
    protected $faker;

    protected function setUp()
    {
        parent::setUp();
        $kernel = static::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->faker = Factory::create();
    }

    public function getUser(): User
    {
        if ($this->user !== null) {
            return $this->user;
        }

        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        $user = $userRepository->findOneBy(['email' => AbstractApiTestCase::STANDARD_USER]);
        if (empty($user)) {
            throw new IncompleteTestError(sprintf('Can`t find user by email "%s"', AbstractApiTestCase::STANDARD_USER));
        }
        return $this->user = $user;
    }
}