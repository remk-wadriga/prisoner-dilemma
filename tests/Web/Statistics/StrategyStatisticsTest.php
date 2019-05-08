<?php

namespace App\Tests\Web\Statistics;

use App\Entity\Strategy;
use Symfony\Component\HttpFoundation\Response;

class StrategyStatisticsTest extends AbstractStatisticsApiTestCase
{
    public function testStatisticsByDates()
    {
        $testKeysID = 'strategy_statics_by_dates';

        // 1. Login as user
        $this->logInAsUser();
        $user = $this->user;

        // 2. Get current users strategy
        $strategy = $this->findStrategy();
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
        $this->checkStatisticsResponse($response, $testKeysID, [
            'bales' => 'double',
            'gamesCount' => 'integer',
            'gameDate' => 'string',
        ]);
    }

    public function testStatisticsByRoundsCount()
    {
        $testKeysID = 'strategy_statics_by_rounds_count';

        // 1. Login as user
        $this->logInAsUser();
        $user = $this->user;

        // 2. Get current users strategy
        $strategy = $this->findStrategy();
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
        $this->checkStatisticsResponse($response, $testKeysID, [
            'bales' => ['double', 'integer'],
            'gamesCount' => 'integer',
            'roundsCount' => 'integer',
        ]);
    }


    private function findStrategy(int $userID = null, int $strategyID = null): ?Strategy
    {
        if ($userID === null && $this->user !== null) {
            $userID = $this->user->getId();
        }
        if ($userID === null) {
            return null;
        }

        if ($strategyID === null) {
            $criteria = ['user' => $userID];
        } else {
            $criteria = ['id' => $strategyID];
        }
        return $this->entityManager->getRepository(Strategy::class)->findOneBy($criteria);
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
