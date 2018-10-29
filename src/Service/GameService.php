<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 23.10.2018
 * Time: 18:28
 */

namespace App\Service;

use App\Entity\Game;
use App\Entity\Types\Enum\DecisionTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\GameServiceException;
use App\Entity\Strategy;
use App\Entity\Decision;
use App\Entity\User;
use App\Entity\Types\Enum\IsEnabledEnum;

class GameService extends AbstractService
{
    private $decisionsService;
    private $gameResultsService;
    private $decisionsTreeForStrategies = [];

    // Game coefficients and attributes
    private $roundsCount = 25;
    private $balesForWin = 15;
    private $balesForLoos = -10;
    private $balesForCooperation = 5;
    private $balesForDraw = 0;
    private $individualResults = [];

    public function __construct(EntityManagerInterface $entityManager, StrategyDecisionsService $decisionsService, GameResultsService $gameResultsService)
    {
        parent::__construct($entityManager);
        $this->decisionsService = $decisionsService;
        $this->gameResultsService = $gameResultsService;
    }

    public function getParams(Game $game = null): array
    {
        $params = [
            'rounds' => $this->roundsCount,
            'balesForWin' => $this->balesForWin,
            'balesForLoos' => $this->balesForLoos,
            'balesForCooperation' => $this->balesForCooperation,
            'balesForDraw' => $this->balesForDraw,
        ];

        if ($game !== null) {
            foreach (array_keys($params) as $name) {
                $getter = 'get' . ucfirst($name);
                $value = $game->$getter();
                if ($value !== null) {
                    $params[$name] = $value;
                }
            }
        }

        return $params;
    }

    public function runGame(User $user, $strategiesIds = [], int $roundsCount = null, int $balesForWin = null, int $balesForLoos = null, int $balesForCooperation = null, int $balesForDraw = null, bool $writeIndividualResults = true): array
    {
        // Create a decisions tree for all strategies (array indexed by strategies Ids)
        $strategies = $this->createDecisionsTreeByStrategiesIds($user, $strategiesIds);

        // For game we need 2 or more strategies
        if (count($strategies) < 2) {
            throw new GameServiceException('It\'s impossible to make game with less then 2 strategies', GameServiceException::CODE_GAME_IMPOSSIBLE);
        }

        // Set game params
        if ($roundsCount !== null) {
            $this->roundsCount = $roundsCount;
        }
        if ($balesForWin !== null) {
            $this->balesForWin = $balesForWin;
        }
        if ($balesForLoos !== null) {
            $this->balesForLoos = $balesForLoos;
        }
        if ($balesForCooperation !== null) {
            $this->balesForCooperation = $balesForCooperation;
        }
        if ($balesForDraw !== null) {
            $this->balesForDraw = $balesForDraw;
        }

        // Write "game started" log
        $this->logInfo('Game started!', [
            'userID' => $user->getId(),
            'strategiesIds' => $strategiesIds,
            'params' => $this->getParams(),
        ]);
        // Remember strategies names
        $strategiesNames = [];
        foreach ($strategies as $strategy) {
            $strategiesNames[$strategy['strategyID']] = $strategy['strategyName'];
        }

        // Start a game!
        $results = $this->makeGameWithStrategiesRecursively($strategies, $writeIndividualResults);

        // Calculate total game sum
        $totalSum = 0;

        // Fulfil results - remove strategies IDs from indexes and add strategy ID and name to result
        foreach ($results as $id => $result) {
            $results[$id] = [
                'id' => $id,
                'name' => isset($strategiesNames[$id]) ? $strategiesNames[$id] : null,
                'result' => $result,
            ];
            $totalSum += $result;
        }

        // Merge results - total score + strategies couples score
        $results = [
            'sum' => $totalSum,
            'total' => array_values($results),
            'individual' => $this->individualResults,
        ];

        // Write "game finished" log
        $this->logInfo('Game finished!', [
            'userID' => $user->getId(),
            'strategiesIds' => $strategiesIds,
            'params' => $this->getParams(),
            'results' => $results,
        ]);

        // Return results
        return $results;
    }

    public function createDecisionsTreeByStrategiesIds(User $user, $strategiesIds = []): array
    {
        // Try to get decisions tree from runtime cache
        $key = md5($user->getId() . ':' . serialize($strategiesIds));
        if (isset($this->decisionsTreeForStrategies[$key])) {
            return $this->decisionsTreeForStrategies[$key];
        }
        $this->decisionsTreeForStrategies[$key] = [];

        // Find user enabled strategies by ids
        $criteria = [
            'user' => $user,
            'status' => IsEnabledEnum::TYPE_ENABLED,
        ];
        if (!empty($strategiesIds)) {
            $criteria['id'] = $strategiesIds;
        }
        /** @var \App\Repository\StrategyRepository $strategyRepository */
        $strategyRepository = $this->entityManager->getRepository(Strategy::class);
        /** @var Strategy[] $strategies */
        $strategies = $strategyRepository->findBy($criteria);
        if (empty($strategies)) {
            throw $this->createNotFoundException('Enabled strategies for user #%s are not found', $user->getId(), $strategiesIds);
        }

        // Get all strategies decisions
        /** @var \App\Repository\DecisionRepository $decisionsRepository */
        $decisionsRepository = $this->entityManager->getRepository(Decision::class);
        /** @var Decision[] $decisions */
        $decisions = $decisionsRepository->findBy(['strategy' => $strategies], ['id' => 'ASC']);
        if (empty($decisions)) {
            throw $this->createNotFoundException('Decisions for user`s #%s strategies are not found', $user->getId(), $strategiesIds);
        }

        // Generate decisions tree for each strategy (using recursively function) and add to runtime cache
        foreach ($strategies as $strategy) {
            $strategyDecisions = [];
            foreach ($decisions as $decisionIndex => $decision) {
                if ($decision->getStrategy()->getId() === $strategy->getId()) {
                    unset($decisions[$decisionIndex]);
                    $strategyDecisions[] = $decision;
                }
            }
            // Using recursive function for create strategy decisions tree
            $strategyDecisions = $this->generateDecisionsTreeRecursively($strategyDecisions);

            if (!empty($strategyDecisions)) {
                $this->decisionsTreeForStrategies[$key][$strategy->getId()] = $strategyDecisions;
            }
        }

        return $this->decisionsTreeForStrategies[$key];
    }

    public function parseResultsData(Game $game)
    {
        // If results not changed - we have nothing to do
        if ($game->getResultsData() === null) {
            return;
        }

        // Remove old results
        foreach ($game->getGameResults() as $gameResult) {
            foreach ($gameResult->getIndividualGameResults() as $individualGameResult) {
                $gameResult->removeIndividualGameResult($individualGameResult);
                $this->entityManager->remove($individualGameResult);
            }
            $game->removeGameResult($gameResult);
            $this->entityManager->remove($gameResult);
        }

        // If new results it's empty array - just return, will be removed old results and that's all
        if (empty($game->getResultsData())) {
            return;
        }


        // If we are here, we have a new game results and should create objects and create structure
        $resultsData = $game->getResultsData();
        $this->checkGameResultsData($resultsData);

        // Create total game results
        foreach ($resultsData['total'] as $totalResultsData) {
            // Create game result
            $gameResult = $this->gameResultsService->createGameResultFromDataArray($totalResultsData);
            $this->entityManager->persist($gameResult);

            // Check is individual results exists in individual results data array
            $strategyID = $gameResult->getStrategy()->getId();
            if (!isset($resultsData['individual'][$strategyID])) {
                throw new GameServiceException(
                    sprintf('Game results data array have an incorrect structure: "total" array has #%s decision, but "individual" is not', $strategyID),
                    GameServiceException::CODE_INVALID_PARAMS
                );
            }

            // Create individual results and add them to game result
            foreach ($resultsData['individual'][$strategyID] as $individualResultData) {
                $individualResult = $this->gameResultsService->createIndividualResultFromDataArray($individualResultData);
                $this->entityManager->persist($individualResult);
                $gameResult->addIndividualGameResult($individualResult);
            }

            // Add game result to game
            $game->addGameResult($gameResult);
        }
    }


    /**
     * @param array $strategies
     * @param bool $writeIndividualResults
     * @return array
     * @throws GameServiceException
     */
    private function makeGameWithStrategiesRecursively(array &$strategies, $writeIndividualResults = true): array
    {
        $results = [];

        // Stop condition = when no more strategies left
        if (empty($strategies)) {
            return $results;
        }

        // First get first strategy from array
        $currentStrategy = array_shift($strategies);

        // Get current strategy ID
        $strategy1ID = $currentStrategy['strategyID'];

        // Create statistic for current strategy
        if (!isset($results[$strategy1ID])) {
            $results[$strategy1ID] = 0;
        }

        // Make game with current strategy and everyone else
        foreach ($strategies as $index => $nexStrategy) {
            // Get next strategy ID
            $strategy2ID = $nexStrategy['strategyID'];

            // Create statistic for next strategy
            if (!isset($results[$strategy2ID])) {
                $results[$strategy2ID] = 0;
            }

            // Make game with these two strategies and sum the results
            $currentResults = $this->makeGameWithTwoDecisionsRecursively($currentStrategy, $nexStrategy);

            // Sum results
            if (isset($currentResults[$strategy1ID])) {
                $results[$strategy1ID] += $currentResults[$strategy1ID];
            }
            if (isset($currentResults[$strategy2ID])) {
                $results[$strategy2ID] += $currentResults[$strategy2ID];
            }

            // Write couples of strategies results
            if ($writeIndividualResults) {
                // Add results in individual of strategies results array
                $this->writeCoupleResultsToIndividualStrategyResults($currentStrategy, $nexStrategy, $currentResults);
                $this->writeCoupleResultsToIndividualStrategyResults($nexStrategy, $currentStrategy, $currentResults);
            }
        }

        // Recursion - make game with strategies which are left and sum all results
        $nextResults = $this->makeGameWithStrategiesRecursively($strategies, $writeIndividualResults);
        foreach ($nextResults as $strategyID => $sum) {
            if (!isset($results[$strategyID])) {
                $results[$strategyID] = 0;
            }
            $results[$strategyID] += $sum;
        }

        // Return results
        return $results;

    }

    private function writeCoupleResultsToIndividualStrategyResults(array $strategy1, array $strategy2, array $results)
    {
        $strategy1ID = $strategy1['strategyID'];
        $strategy2ID = $strategy2['strategyID'];
        if (!isset($results[$strategy1ID]) || !isset($results[$strategy2ID])) {
            return;
        }
        if (!isset($this->individualResults[$strategy1ID])) {
            $this->individualResults[$strategy1ID] = [];
        }
        if (!isset($this->individualResults[$strategy1ID][$strategy2ID])) {
            $this->individualResults[$strategy1ID][$strategy2ID] = [
                'result' => 0,
                'partnerResult' => 0,
                'partnerID' => $strategy2ID,
                'partnerName' => $strategy2['strategyName'],
            ];
        }
        $this->individualResults[$strategy1ID][$strategy2ID]['result'] += $results[$strategy1ID];
        $this->individualResults[$strategy1ID][$strategy2ID]['partnerResult'] += $results[$strategy2ID];
    }

    /**
     * @param array $rootDecision1
     * @param array $rootDecision2
     * @param array|null $decision1
     * @param array|null $decision2
     * @param string|null $lastAnswer1
     * @param string|null $lastAnswer2
     * @param int $round
     * @return array
     * @throws GameServiceException
     */
    private function makeGameWithTwoDecisionsRecursively(array $rootDecision1, array $rootDecision2, array $decision1 = null, array $decision2 = null, $lastAnswer1 = null, $lastAnswer2 = null, $round = 1): array
    {
        $results = [];

        // Stop condition - when current round number bigger then game rounds count (it means that game is over)
        if ($round > $this->roundsCount) {
            return $results;
        }

        // In first step our decisions is just root decisions. Also it can be useful when the strategy steps is over -
        //  in this case the strategy wil be just started from it's first step
        if (empty($decision1)) {
            $decision1 = $rootDecision1;
        }
        if (empty($decision2)) {
            $decision2 = $rootDecision2;
        }

        // Check decisions params
        $this->checkGameDecisionElement($decision1);
        $this->checkGameDecisionElement($decision2);

        // Get strategies IDs
        $strategy1ID = $decision1['strategyID'];
        $strategy2ID = $decision2['strategyID'];

        // Create statistic for strategies
        if (!isset($results[$strategy1ID])) {
            $results[$strategy1ID] = 0;
        }
        if (!isset($results[$strategy2ID])) {
            $results[$strategy2ID] = 0;
        }

        // Get "Yes", "No", "random" and "copy" decision types
        $yes = DecisionTypeEnum::TYPE_ACCEPT;
        $no = DecisionTypeEnum::TYPE_REFUSE;
        $random = DecisionTypeEnum::TYPE_RANDOM;
        $copy = DecisionTypeEnum::TYPE_COPY;

        // If last answers are not set - that's means that it's first round, so "copy partner action" means just "make random decision"
        if ($lastAnswer1 === null) {
            $lastAnswer1 = $random;
        }
        if ($lastAnswer2 === null) {
            $lastAnswer2 = $random;
        }

        // Get types for both decisions
        $answer1 = $decision1['type'];
        $answer2 = $decision2['type'];

        // If first answer is "copy partner action" - that's means that we will copy partner last action
        if ($answer1 === $copy) {
            $answer1 = $lastAnswer2;
        }
        if ($answer2 === $copy) {
            $answer2 = $lastAnswer1;
        }

        // If one of answers is random - set for it "Yes" or "No" by random
        if ($answer1 === $random) {
            $answer1 = $this->faker->boolean ? $yes : $no;
        }
        if ($answer2 === $random) {
            $answer2 = $this->faker->boolean ? $yes : $no;
        }

        // So, check who won
        // 1. First say "Yes", second say "No" - second won
        if ($answer1 === $yes && $answer2 === $no) {
            $results[$strategy1ID] += $this->balesForLoos;
            $results[$strategy2ID] += $this->balesForWin;
        // 2. First say "Yes" and second say "Yes" - nobody won, but they cooperated
        } elseif ($answer1 === $yes && $answer2 === $yes) {
            $results[$strategy1ID] += $this->balesForCooperation;
            $results[$strategy2ID] += $this->balesForCooperation;
        // 3. First say "No" and second say "Yes" - first won
        } elseif ($answer1 === $no && $answer2 === $yes) {
            $results[$strategy1ID] += $this->balesForWin;
            $results[$strategy2ID] += $this->balesForLoos;
        // 4. First say "No" and second say "No" - it's draw
        } elseif ($answer1 === $no && $answer2 === $no) {
            $results[$strategy1ID] += $this->balesForDraw;
            $results[$strategy2ID] += $this->balesForDraw;
        }

        // So, now we know who is won, it's time to find out the next decision for both decisions
        $nextDecision1 = null;
        $nextDecision2 = null;
        // Next decision depends from current partner decision
        $partnerSayYesNextDecision1 = isset($decision1['children'][0]) ? $decision1['children'][0] : null;
        $partnerSayNoNextDecision1 = isset($decision1['children'][1]) ? $decision1['children'][1] : null;
        $partnerSayYesNextDecision2 = isset($decision2['children'][0]) ? $decision2['children'][0] : null;
        $partnerSayNoNextDecision2 = isset($decision2['children'][1]) ? $decision2['children'][1] : null;
        // If strategy in current step has only one decision for -
        //  it means that it just has two similar decisions for both partner answers
        if ($partnerSayNoNextDecision1 === null) {
            $partnerSayNoNextDecision1 = $partnerSayYesNextDecision1;
        }
        if ($partnerSayNoNextDecision2 === null) {
            $partnerSayNoNextDecision2 = $partnerSayYesNextDecision2;
        }
        // Finally - next decisions...
        $nextDecision1 = $answer2 === $yes ? $partnerSayYesNextDecision1 : $partnerSayNoNextDecision1;
        $nextDecision2 = $answer1 === $yes ? $partnerSayYesNextDecision2 : $partnerSayNoNextDecision2;

        // Next - increment round number and start next round
        $round++;

        // Play next decisions and sum results fro each strategy - recursively
        $nextResults = $this->makeGameWithTwoDecisionsRecursively($rootDecision1, $rootDecision2, $nextDecision1, $nextDecision2, $answer1, $answer2, $round);
        if (isset($nextResults[$strategy1ID])) {
            $results[$strategy1ID] += $nextResults[$strategy1ID];
        }
        if (isset($nextResults[$strategy2ID])) {
            $results[$strategy2ID] += $nextResults[$strategy2ID];
        }

        // Return results
        return $results;
    }

    /**
     * @param Decision[] $decisions
     * @param array|null $stepElement
     * @return Decision[]
     */
    private function generateDecisionsTreeRecursively(array &$decisions, array &$stepElement = []): array
    {
        // Stop condition - when no more decisions left
        if (empty($decisions)) {
            return [];
        }
        // Find root element - for first step
        if (empty($stepElement)) {
            foreach ($decisions as $index => $decision) {
                if ($decision->getParent() === null) {
                    unset($decisions[$index]);
                    $stepElement = [
                        'strategyID' => $decision->getStrategy()->getId(),
                        'strategyName' => $decision->getStrategy()->getName(),
                        'id' => $decision->getId(),
                        'parentID' => null,
                        'type' => $decision->getType(),
                        'children' => [],
                    ];
                    break;
                }
            }
        }
        // It's impossible to create a decisions tree without a root element
        if (empty($stepElement)) {
            return [];
        }
        // Add children to current step decision recursively
        foreach ($decisions as $index => $decision) {
            // Find the child of current decision
            if ($decision->getParent()->getId() == $stepElement['id']) {
                // First - throw child decision from decisions array
                unset($decisions[$index]);
                // Second - create child element from child decision object
                $child = [
                    'strategyID' => $decision->getStrategy()->getId(),
                    'strategyName' => $decision->getStrategy()->getName(),
                    'id' => $decision->getId(),
                    'parentID' => $stepElement['id'],
                    'type' => $decision->getType(),
                    'children' => [],
                ];
                // Third - try to find two children for child element and add them to it (with recursively fulfilment "second-level" children)
                for ($i = 0; $i < 2; $i++) {
                    $secondLevelChild = $this->generateDecisionsTreeRecursively($decisions, $child);
                    if (!empty($secondLevelChild) && $secondLevelChild['parentID'] == $child['id']) {
                        $child['children'][] = $secondLevelChild;
                    }
                }
                // Add child to current decision element
                $stepElement['children'][] = $child;
            }
        }

        // Return current decision element
        return $stepElement;
    }

    /**
     * @param string $message
     * @param int $userID
     * @param array $strategiesIds
     * @return GameServiceException
     */
    private function createNotFoundException(string $message, int $userID, array $strategiesIds): GameServiceException
    {
        $message = sprintf($message, $userID);
        if (!empty($strategiesIds)) {
            $message = $message . '. Strategies Ids: ' . implode(',', $strategiesIds);
        }
        return new GameServiceException($message, GameServiceException::CODE_STRATEGIES_NOT_FOUND);
    }

    /**
     * Check game decision element - it must have "strategyID", "type" and "children" attributes
     * "strategyID" - integer (> 0)
     * "type" - string (one of DecisionTypeEnum::getAvailableTypes())
     * "children" - array
     *
     * @param array $decision
     * @throws GameServiceException
     */
    private function checkGameDecisionElement(array $decision)
    {
        if (!isset($decision['id'])) {
            throw new GameServiceException('Every game decision must have a "id" attribute', GameServiceException::CODE_INVALID_PARAMS);
        }
        if (!isset($decision['strategyID'])) {
            throw new GameServiceException('Every game decision must have a "strategyID" attribute', GameServiceException::CODE_INVALID_PARAMS);
        }
        if ((int)$decision['strategyID'] === 0) {
            throw new GameServiceException(sprintf('Game decision #%s has an incorrect value of "strategyID" attribute, It must be an integer (> 0), but "%s" given',
                $decision['id'], $decision['strategyID']),
                GameServiceException::CODE_INVALID_PARAMS);
        }
        if (!isset($decision['type'])) {
            throw new GameServiceException('Every game decision must have a "type" attribute', GameServiceException::CODE_INVALID_PARAMS);
        }
        if (!in_array((string)$decision['type'], DecisionTypeEnum::getAvailableTypes())) {
            throw new GameServiceException(sprintf('Game decision #%s has an incorrect value of "type" attribute, It must be one of [%s], but "%s" given',
                    $decision['id'],
                 '"' . implode('", "', DecisionTypeEnum::getAvailableTypes()) . '"',
                    (string)$decision['type']
                ),
                GameServiceException::CODE_INVALID_PARAMS);
        }
        if (!isset($decision['children'])) {
            throw new GameServiceException('Every game decision must have a "children" attribute', GameServiceException::CODE_INVALID_PARAMS);
        }
        if (!is_array($decision['children'])) {
            throw new GameServiceException(sprintf('Game decision #%s has an incorrect value of "children" attribute, It must be an array, but "%s" given',
                $decision['id'], (string)$decision['children']),
                GameServiceException::CODE_INVALID_PARAMS);
        }
    }

    /**
     * Check game results data array - it must have "total" and "individual" not empty arrays attributes
     *
     * @param array $result
     * @throws GameServiceException
     */
    private function checkGameResultsData(array $result)
    {
        if (!isset($result['total']) || !is_array($result['total']) || empty($result['total'])) {
            throw new GameServiceException('Results array mus have a "total" kay and it\'s should be a not empty array', GameServiceException::CODE_INVALID_PARAMS);
        }
        if (!isset($result['individual']) || !is_array($result['individual']) || empty($result['individual'])) {
            throw new GameServiceException('Results array mus have a "individual" kay and it\'s should be a not empty array', GameServiceException::CODE_INVALID_PARAMS);
        }
    }
}