<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 23.10.2018
 * Time: 18:28
 */

namespace App\Service;

use App\Entity\Types\Enum\DecisionTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use App\Exception\GameException;
use App\Entity\Strategy;
use App\Entity\Decision;
use App\Entity\User;
use App\Entity\Types\Enum\IsEnabledEnum;

class GameService extends AbstractService
{
    private $decisionsService;
    private $decisionsTreeForStrategies = [];

    // Game coefficients and attributes
    private $roundsCount = 25;
    private $balesForWin = 15;
    private $balesForLoos = -10;
    private $balesForCooperation = 5;
    private $balesForDraw = 0;
    private $coupesStrategiesResults = [];

    public function __construct(EntityManagerInterface $entityManager, StrategyDecisionsService $decisionsService)
    {
        parent::__construct($entityManager);
        $this->decisionsService = $decisionsService;
    }

    public function runGame(User $user, $strategiesIds = [], $writeCoupesStrategiesResults = true): array
    {
        // Create a decisions tree for all strategies (array indexed by strategies Ids)
        $strategies = $this->createDecisionsTreeByStrategiesIds($user, $strategiesIds);

        // For game we need 2 or more strategies
        if (count($strategies) < 2) {
            throw new GameException('It\'s impossible to make game with less then 2 strategies', GameException::CODE_GAME_IMPOSSIBLE);
        }

        // Write "game started" log
        $this->logInfo('Game started!', [
            'userID' => $user->getId(),
            'strategiesIds' => $strategiesIds,
        ]);

        // Start a game!
        $results = $this->makeGameWithStrategiesRecursively($strategies, $writeCoupesStrategiesResults);

        // Merge results - total score + strategies couples score
        $results = [
            'total' => $results,
            'couples' => $this->coupesStrategiesResults,
        ];

        // Write "game finished" log
        $this->logInfo('Game finished!', [
            'userID' => $user->getId(),
            'strategiesIds' => $strategiesIds,
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
            $this->decisionsTreeForStrategies[$key][] = $this->generateDecisionsTreeRecursively($strategyDecisions);
        }

        return $this->decisionsTreeForStrategies[$key];
    }


    /**
     * @param array $strategies
     * @param bool $writeCoupesStrategiesResults
     * @return array
     * @throws GameException
     */
    private function makeGameWithStrategiesRecursively(array &$strategies, $writeCoupesStrategiesResults = true): array
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
            if ($writeCoupesStrategiesResults) {
                // Add results in couples of strategies results array
                $coupleResultsKey = $strategy1ID . ':' . $strategy2ID;
                if (!isset($this->coupesStrategiesResults[$coupleResultsKey])) {
                    $this->coupesStrategiesResults[$coupleResultsKey] = [];
                }
                if (!isset($this->coupesStrategiesResults[$coupleResultsKey][$strategy1ID])) {
                    $this->coupesStrategiesResults[$coupleResultsKey][$strategy1ID] = 0;
                }
                if (!isset($this->coupesStrategiesResults[$coupleResultsKey][$strategy2ID])) {
                    $this->coupesStrategiesResults[$coupleResultsKey][$strategy2ID] = 0;
                }
                if (isset($currentResults[$strategy1ID])) {
                    $this->coupesStrategiesResults[$coupleResultsKey][$strategy1ID] += $currentResults[$strategy1ID];
                }
                if (isset($currentResults[$strategy2ID])) {
                    $this->coupesStrategiesResults[$coupleResultsKey][$strategy2ID] += $currentResults[$strategy2ID];
                }
            }
        }

        // Recursion - make game with strategies which are left and sum all results
        $nextResults = $this->makeGameWithStrategiesRecursively($strategies, $writeCoupesStrategiesResults);
        foreach ($nextResults as $strategyID => $sum) {
            if (!isset($results[$strategyID])) {
                $results[$strategyID] = 0;
            }
            $results[$strategyID] += $sum;
        }

        // Return results
        return $results;

    }

    /**
     * @param array $rootDecision1
     * @param array $rootDecision2
     * @param array|null $decision1
     * @param array|null $decision2
     * @param int $round
     * @return array
     * @throws GameException
     */
    private function makeGameWithTwoDecisionsRecursively(array $rootDecision1, array $rootDecision2, array $decision1 = null, array $decision2 = null, $round = 1): array
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

        // Get "Yes", "No" and "random" decision types
        $yes = DecisionTypeEnum::TYPE_ACCEPT;
        $no = DecisionTypeEnum::TYPE_REFUSE;
        $random = DecisionTypeEnum::TYPE_RANDOM;

        // Get types for both decisions
        $answer1 = $decision1['type'];
        $answer2 = $decision2['type'];

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
        // Finally - next decisions...
        $nextDecision1 = $answer2 === $yes ? $partnerSayYesNextDecision1 : $partnerSayNoNextDecision1;
        $nextDecision2 = $answer1 === $yes ? $partnerSayYesNextDecision2 : $partnerSayNoNextDecision2;

        // Next - increment round number and start next round
        $round++;

        // Play next decisions and sum results fro each strategy - recursively
        $nextResults = $this->makeGameWithTwoDecisionsRecursively($rootDecision1, $rootDecision2, $nextDecision1, $nextDecision2, $round);
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
     * @return GameException
     */
    private function createNotFoundException(string $message, int $userID, array $strategiesIds): GameException
    {
        $message = sprintf($message, $userID);
        if (!empty($strategiesIds)) {
            $message = $message . '. Strategies Ids: ' . implode(',', $strategiesIds);
        }
        return new GameException($message, GameException::CODE_STRATEGIES_NOT_FOUND);
    }

    /**
     * Check game decision element - it must have "strategyID", "type" and "children" attributes
     * "strategyID" - integer (> 0)
     * "type" - string (one of DecisionTypeEnum::getAvailableTypes())
     * "children" - array
     *
     * @param array $decision
     * @throws GameException
     */
    private function checkGameDecisionElement(array $decision)
    {
        if (!isset($decision['id'])) {
            throw new GameException('Every game decision must have a "id" attribute', GameException::CODE_INVALID_PARAMS);
        }
        if (!isset($decision['strategyID'])) {
            throw new GameException('Every game decision must have a "strategyID" attribute', GameException::CODE_INVALID_PARAMS);
        }
        if ((int)$decision['strategyID'] === 0) {
            throw new GameException(sprintf('Game decision #%s has an incorrect value of "strategyID" attribute, It must be an integer (> 0), but "%s" given',
                $decision['id'], $decision['strategyID']),
                GameException::CODE_INVALID_PARAMS);
        }
        if (!isset($decision['type'])) {
            throw new GameException('Every game decision must have a "type" attribute', GameException::CODE_INVALID_PARAMS);
        }
        if (!in_array((string)$decision['type'], DecisionTypeEnum::getAvailableTypes())) {
            throw new GameException(sprintf('Game decision #%s has an incorrect value of "type" attribute, It must be one of [%s], but "%s" given',
                    $decision['id'],
                 '"' . implode('", "', DecisionTypeEnum::getAvailableTypes()) . '"',
                    (string)$decision['type']
                ),
                GameException::CODE_INVALID_PARAMS);
        }
        if (!isset($decision['children'])) {
            throw new GameException('Every game decision must have a "children" attribute', GameException::CODE_INVALID_PARAMS);
        }
        if (!is_array($decision['children'])) {
            throw new GameException(sprintf('Game decision #%s has an incorrect value of "children" attribute, It must be an array, but "%s" given',
                $decision['id'], (string)$decision['children']),
                GameException::CODE_INVALID_PARAMS);
        }
    }
}