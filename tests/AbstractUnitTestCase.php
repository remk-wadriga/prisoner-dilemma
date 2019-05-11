<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 23.10.2018
 * Time: 11:36
 */

namespace App\Tests;

use App\Entity\GameResult;
use App\Repository\Service\TotalStatisticsRepository;
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

    /**
     * @var User
     */
    protected $randomUser;

    /** @var \Faker\Generator */
    protected $faker;

    protected function setUp()
    {
        parent::setUp();
        $kernel = static::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->faker = Factory::create();
    }

    protected function getParam($name)
    {
        $container = self::$kernel->getContainer();
        if (!$container->hasParameter($name)) {
            return null;
        }
        return str_replace('0/0', '%', $container->getParameter($name));
    }

    protected function enableDoctrineFilters($filters, $filterPrefix = '', $postfix = '_filter')
    {
        foreach ($filters as $param => $value) {
            if ($value === null) {
                continue;
            }
            $param = $filterPrefix . $param;
            $doctrineFilterName = $param . $postfix;
            if ($this->entityManager->getFilters()->has($doctrineFilterName)) {
                $filter = $this->entityManager->getFilters()->enable($doctrineFilterName);
                $filter->setParameter($param, $value);
            }
        }
    }

    protected function getRandomDatesPeriod()
    {
        /** @var TotalStatisticsRepository $repository */
        $repository = new TotalStatisticsRepository($this->entityManager, static::$container);
        $dates = $repository->getFirstAndLastGamesDates($this->getRandomUser());
        $faker = Factory::create();
        $dates['toDate'] = (new \DateTime($dates['end']))
            ->modify(sprintf('-%s days', $faker->numberBetween(0, 5)))
            ->format($this->getParam('backend_date_format'));
        $dates['fromDate'] = (new \DateTime($dates['toDate']))
            ->modify(sprintf('-%s days', $faker->numberBetween(1, 10)))
            ->format($this->getParam('backend_date_format'));
        unset($dates['start'], $dates['end']);
        return $dates;
    }

    protected function createRandomGameParamsFilters()
    {
        $faker = Factory::create();
        return [
            'game_roundsCount' => $faker->numberBetween(10, 100),
            'game_balesForWin' => $faker->numberBetween(20, 50),
            'game_balesForLoos' => $faker->numberBetween(-20, 0),
            'game_balesForCooperation' => $faker->numberBetween(-10, 10),
            'game_balesForDraw' => $faker->numberBetween(-10, 10),
        ];
    }

    protected function getRandomUser(): User
    {
        if ($this->randomUser !== null) {
            return $this->randomUser;
        }
        $userRepository = $this->entityManager->getRepository(User::class);
        $gameResultRepository = $this->entityManager->getRepository(GameResult::class);

        $strategiesIDsQuery = $gameResultRepository->createQueryBuilder('gr')
            ->select('u.id')
            ->innerJoin('gr.strategy', 's')
            ->innerJoin('s.user', 'u')
            ->setMaxResults(100)
        ;

        $ids = array_map(function ($result) { return intval($result['id']); }, $strategiesIDsQuery->getQuery()->getScalarResult());
        $faker = Factory::create();
        $id = $faker->randomElement($ids);

        return $this->randomUser = $userRepository->findOneBy(['id' => $id]);
    }
}