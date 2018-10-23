<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 23.10.2018
 * Time: 18:28
 */

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Exception\GameException;
use App\Entity\Strategy;
use App\Entity\Decision;
use App\Entity\User;
use App\Entity\Types\Enum\IsEnabledEnum;

class GameService extends AbstractService
{
    private $decisionsService;

    public function __construct(EntityManagerInterface $entityManager, StrategyDecisionsService $decisionsService)
    {
        parent::__construct($entityManager);
        $this->decisionsService = $decisionsService;
    }

    public function runGame(User $user, $strategiesIds = [])
    {
        $strategies = $this->createDecisionsTreeByStrategiesIds($user, $strategiesIds);



        return $strategies;
    }


    public function createDecisionsTreeByStrategiesIds(User $user, $strategiesIds = []): array
    {
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

        // Get strategies IDs array and index strategies array by it
        foreach ($strategies as $index => $strategy) {
            $strategyDecisions = [];
            foreach ($decisions as $decisionIndex => $decision) {
                if ($decision->getStrategy()->getId() === $strategy->getId()) {
                    unset($decisions[$decisionIndex]);
                    $strategyDecisions[] = $decision;
                }
            }
            $strategies[$index] = [
                'strategy' => $strategy,
                'decisions' => $this->generateDecisionsTreeRecursively($strategyDecisions),
            ];
        }

        return $strategies;
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

        // Find first root element - for first step
        if (empty($stepElement)) {
            foreach ($decisions as $index => $decision) {
                if ($decision->getParent() === null) {
                    unset($decisions[$index]);
                    $stepElement = [
                        'id' => $decision->getId(),
                        'parentID' => null,
                        'type' => $decision->getType(),
                        'children' => [],
                    ];
                    break;
                }
            }
        }
        if (empty($stepElement)) {
            return [];
        }

        foreach ($decisions as $index => $decision) {
            if ($decision->getParent()->getId() == $stepElement['id']) {
                unset($decisions[$index]);
                $child = [
                    'id' => $decision->getId(),
                    'parentID' => $stepElement['id'],
                    'type' => $decision->getType(),
                    'children' => [],
                ];
                for ($i = 0; $i < 2; $i++) {
                    $childLevel2 = $this->generateDecisionsTreeRecursively($decisions, $child);
                    if (!empty($childLevel2) && $childLevel2['parentID'] == $child['id']) {
                        $child['children'][] = $childLevel2;
                    }
                }
                $stepElement['children'][] = $child;
            }
        }

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
}