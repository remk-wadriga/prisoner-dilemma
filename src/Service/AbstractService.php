<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 20.09.2018
 * Time: 10:38
 */

namespace App\Service;

use Faker\Factory;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractService
{
    /** @var \Faker\Generator */
    protected $faker;
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->faker = Factory::create();
    }
}