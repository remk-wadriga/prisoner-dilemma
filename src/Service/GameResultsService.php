<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 29.10.2018
 * Time: 00:40
 */

namespace App\Service;

use App\Entity\Game;
use App\Entity\GameResult;
use App\Entity\IndividualGameResult;
use App\Entity\Strategy;
use App\Exception\GameServiceException;

class GameResultsService extends AbstractService
{
    public function createGameResultFromDataArray(array $data): GameResult
    {
        // Check data
        $this->checkGameResultElement($data);

        // Try to find a strategy
        /** @var \App\Entity\Strategy $decision */
        $strategy = $this->entityManager->getRepository(Strategy::class)->find($data['id']);
        if ($strategy === null) {
            throw new GameServiceException(
                sprintf('Invalid data structure of "total" game data array. The strategy #%s is not found', $data['id']),
                GameServiceException::CODE_INVALID_PARAMS
            );
        }

        // Create new result
        $result = (new GameResult())
            ->setStrategy($strategy)
            ->setResult($data['result'])
        ;

        return $result;
    }

    public function createIndividualResultFromDataArray(array $data): IndividualGameResult
    {
        // Check data
        $this->checkGameIndividualResultElement($data);

        // Try to find a strategy
        /** @var \App\Entity\Strategy $decision */
        $strategy = $this->entityManager->getRepository(Strategy::class)->find($data['partnerID']);
        if ($strategy === null) {
            throw new GameServiceException(
                sprintf('Invalid data structure of "individual" game data array. The strategy #%s is not found', $data['partnerID']),
                GameServiceException::CODE_INVALID_PARAMS
            );
        }

        // Create new result
        $individualResult = (new IndividualGameResult())
            ->setPartner($strategy)
            ->setResult($data['result'])
            ->setPartnerResult($data['partnerResult'])
        ;

        return $individualResult;
    }

    public function parseGameResultsData(Game $game): array
    {
        // 1. Check is game has results
        if ($game->getGameResults()->count() === 0) {
            return [];
        }

        // 2. Create total game results and individual game results array
        $sum = 0;
        $totalResults = [];
        $individualResults = [];
        foreach ($game->getGameResults() as $totalResult) {
            foreach ($totalResult->getIndividualGameResults() as $individualResult) {
                $strategyID = $totalResult->getStrategy()->getId();
                if (!isset($individualResults[$strategyID])) {
                    $individualResults[$strategyID] = [];
                }
                $individualResults[$strategyID][$individualResult->getPartner()->getId()] = [
                    'result' => $individualResult->getResult(),
                    'partnerResult' => $individualResult->getPartnerResult(),
                    'partnerID' => $individualResult->getPartner()->getId(),
                    'partnerName' => $individualResult->getPartner()->getName(),
                ];
            }
            $totalResults[] = [
                'id' => $totalResult->getStrategy()->getId(),
                'name' => $totalResult->getStrategy()->getName(),
                'result' => $totalResult->getResult(),
            ];

            $sum += $totalResult->getResult();
        }

        // 3. Return results
        return [
            'results' => [
                'sum' => $sum,
                'total' => $totalResults,
                'individual' => $individualResults,
            ],
        ];
    }

    /**
     * Check game result element - it must have "id" and "result" attributes
     * "id" - integer (> 0)
     * "result" - integer (> 0)
     *
     * @param array $data
     * @throws GameServiceException
     */
    private function checkGameResultElement(array $data)
    {
        $baseMessage = 'Invalid data structure of "total" game data array. It\'s must have a "%s" key and it\'s mus be not empty %s';
        if (!isset($data['id']) || (int)$data['id'] === 0) {
            throw new GameServiceException(sprintf($baseMessage, 'id', 'integer'), GameServiceException::CODE_INVALID_PARAMS);
        }
        if (!isset($data['result']) || (int)$data['result'] === 0) {
            throw new GameServiceException(sprintf($baseMessage, 'result', 'integer'), GameServiceException::CODE_INVALID_PARAMS);
        }
    }

    /**
     * Check game individual result element - it must have "result", "partnerResult" and "partnerID" attributes
     * "result" - integer (> 0)
     * "partnerResult" - integer (> 0)
     * "partnerID" - integer (> 0)
     *
     * @param array $data
     * @throws GameServiceException
     */
    private function checkGameIndividualResultElement(array $data)
    {
        $baseMessage = 'Invalid structure of "individual" game data array. It\'s must have a "%s" key and it\'s mus be not empty %s';
        if (!isset($data['result']) || (int)$data['result'] === 0) {
            throw new GameServiceException(sprintf($baseMessage, 'result', 'integer'), GameServiceException::CODE_INVALID_PARAMS);
        }
        if (!isset($data['partnerResult']) || (int)$data['partnerResult'] === 0) {
            throw new GameServiceException(sprintf($baseMessage, 'partnerResult', 'integer'), GameServiceException::CODE_INVALID_PARAMS);
        }
        if (!isset($data['partnerID']) || (int)$data['partnerID'] === 0) {
            throw new GameServiceException(sprintf($baseMessage, 'partnerID', 'integer'), GameServiceException::CODE_INVALID_PARAMS);
        }
    }
}