<?php


namespace App\Tests\Web\Statistics;

use App\Entity\Game;
use Symfony\Component\HttpFoundation\Response;

class GameStatisticsTest extends AbstractStatisticsApiTestCase
{
    public function testStatisticsByDates()
    {
        $testKeysID = 'game_statics_by_strategies';

        // 1. Login as user
        $this->logInAsUser();
        $user = $this->user;

        // 2. Get current users game
        $game = $this->findGame();
        $this->assertNotEmpty($game, sprintf('User #%s has no strategies!', $user->getId()));

        // 3. Get some different users game and send request. But if no one strategy is found - it's ok
        $notUserGame = $this->findNotUserGame();
        if ($notUserGame !== null) {
            $response = $this->request(['game_statistics_by_strategies', ['id' => $notUserGame->getId()]]);
            $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatus(),
                sprintf('Wrong test "%s" response format, status code must be equal to %s, but it is not. It is: %s. The content is: %s',
                    $testKeysID, Response::HTTP_FORBIDDEN, $response->getStatus(), $response->getContent()));
        }

        // 4. Get game statistics and check it
        $response = $this->request(['game_statistics_by_strategies', ['id' => $game->getId()]]);
        $this->checkStatisticsResponse($response, $testKeysID, [
            'strategy' => 'string',
            'bales' => 'integer',
        ]);
    }


    private function findGame(int $userID = null, int $gameID = null): ?Game
    {
        if ($userID === null && $this->user !== null) {
            $userID = $this->user->getId();
        }
        if ($userID === null) {
            return null;
        }

        if ($gameID === null) {
            $criteria = ['user' => $userID];
        } else {
            $criteria = ['id' => $gameID];
        }
        return $this->entityManager->getRepository(Game::class)->findOneBy($criteria);
    }

    private function findNotUserGame(int $userID = null): ?Game
    {
        if ($userID === null && $this->user !== null) {
            $userID = $this->user->getId();
        }
        if ($userID === null) {
            return null;
        }
        return $this->entityManager->getRepository(Game::class)->createQueryBuilder('g')
            ->andWhere('g.user != :user')
            ->setParameter('user', $userID)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}