<?php

namespace App\Tests\Web\Statistics;

use App\Entity\GameResult;
use App\Entity\Strategy;
use Symfony\Component\HttpFoundation\Response;
use Faker\Factory;

class StrategyStatisticsTest extends AbstractStatisticsApiTestCase
{
    private $randomStrategy;

    public function AAtestStatisticsByDates()
    {
        $testKeysID = 'strategy_statics_by_dates';

        // 1. Login as user
        $this->logInAsUser();
        $user = $this->user;

        // 2. Get current users strategy
        $strategy = $this->findRandomStrategy();
        $this->assertNotEmpty($strategy, sprintf('User #%s has no strategies!', $user->getId()));

        // 3. Get some different users strategy and send request. But if no one strategy is found - it's ok
        $notUserStrategy = $this->getNotUserStrategy();
        if ($notUserStrategy !== null) {
            $response = $this->request(['strategy_statistics_by_dates', ['id' => $notUserStrategy->getId()]]);
            $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatus(),
                sprintf('Wrong test "%s" response format, status code must be equal to %s, but it is not. It is: %s. The content is: %s',
                    $testKeysID, Response::HTTP_FORBIDDEN, $response->getStatus(), $response->getContent()));
        }

        // 4. Get strategy statistics and check it
        $response = $this->request(['strategy_statistics_by_dates', ['id' => $strategy->getId()]]);
        $this->checkResponseParams($response, $testKeysID, [
            'bales' => 'double',
            'gamesCount' => 'integer',
            'gameDate' => 'string',
        ]);
    }

    public function AAtestStatisticsByRoundsCount()
    {
        $testKeysID = 'strategy_statics_by_rounds_count';

        // 1. Login as user
        $this->logInAsUser();
        $user = $this->user;

        // 2. Get current users strategy
        $strategy = $this->findRandomStrategy();
        $this->assertNotEmpty($strategy, sprintf('User #%s has no strategies!', $user->getId()));

        // 3. Get some different users strategy and send request. But if no one strategy is found - it's ok
        $notUserStrategy = $this->getNotUserStrategy();
        if ($notUserStrategy !== null) {
            $response = $this->request(['strategy_statistics_by_rounds_count', ['id' => $notUserStrategy->getId()]]);
            $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatus(),
                sprintf('Wrong test "%s" response format, status code must be equal to %s, but it is not. It is: %s. The content is: %s',
                    $testKeysID, Response::HTTP_FORBIDDEN, $response->getStatus(), $response->getContent()));
        }

        // 4. Get strategy statistics and check it
        $response = $this->request(['strategy_statistics_by_rounds_count', ['id' => $strategy->getId()]]);
        $this->checkResponseParams($response, $testKeysID, [
            'bales' => ['double', 'integer'],
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
        ]);
    }

    public function testStatisticsDatesParams()
    {
        $testKeysID = 'strategy_statics_dates_range';

        // 1. Login as user
        $this->logInAsUser();
        $user = $this->user;

        // 2. Get current users strategy
        $strategy = $this->findRandomStrategy();
        $this->assertNotEmpty($strategy, sprintf('User #%s has no strategies!', $user->getId()));

        // 3. Make request
        $response = $this->request(['strategy_statistics_dates', ['id' => $strategy->getId()]]);

        // 3. Check response params
        $this->checkResponseParams($response, $testKeysID, [
            'start' => 'string',
            'end' => 'string',
        ], true);
    }


    private function findRandomStrategy(): ?Strategy
    {
        if ($this->randomStrategy !== null) {
            return $this->randomStrategy;
        }

        $strategiesIDsQuery = $this->entityManager->getRepository(GameResult::class)->createQueryBuilder('gr')
            ->select('DISTINCT(s.id) AS id')
            ->innerJoin('gr.strategy', 's')
            ->andWhere('s.user = :user')
            ->setParameter('user', $this->user)
            ->setMaxResults(100)
        ;
        $ids = array_map(function ($res) { return intval($res['id']); }, $strategiesIDsQuery->getQuery()->getArrayResult());
        $faker = Factory::create();

        return $this->entityManager->getRepository(Strategy::class)->findOneBy(['id' => $faker->randomElement($ids)]);
    }

    private function getNotUserStrategy(int $userID = null): ?Strategy
    {
        if ($userID === null && $this->user !== null) {
            $userID = $this->user->getId();
        }
        if ($userID === null) {
            return null;
        }
        return $strategy = $this->entityManager->getRepository(Strategy::class)->createQueryBuilder('s')
            ->andWhere('s.user != :user')
            ->setParameter('user', $userID)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
