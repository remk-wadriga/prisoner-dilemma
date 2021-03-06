<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 23.10.2018
 * Time: 14:23
 */

namespace App\Tests\Unit;

use App\Entity\Decision;
use App\Entity\Strategy;
use App\Tests\AbstractUnitTestCase;
use App\Service\StrategyService;
use App\Service\StrategyDecisionsService;

class BaseStrategyTestCase extends AbstractUnitTestCase
{
    protected $strategyDecisionsService;
    protected $strategyService;

    protected function getStrategyService(): StrategyService
    {
        if ($this->strategyService !== null) {
            return $this->strategyService;
        }
        return $this->strategyService = new StrategyService($this->entityManager, $this->getStrategyDecisionsService(), self::$kernel->getContainer());
    }

    protected function getStrategyDecisionsService(): StrategyDecisionsService
    {
        if ($this->strategyDecisionsService !== null) {
            return $this->strategyDecisionsService;
        }
        return $this->strategyDecisionsService = new StrategyDecisionsService($this->entityManager, self::$kernel->getContainer());
    }

    protected function calculateStrategyChildrenRecursively(Strategy $strategy, Decision $decision = null)
    {
        // 1. Check is strategy has decisions
        if ($strategy->getDecisions()->count() === 0) {
            return 0;
        }

        // 2. Get strategy root decision (for first step)
        if ($decision === null) {
            $decision = $strategy->getDecisions()->current();
        }

        // 3. Stop condition - when decision has no children
        if ($decision->getChildren()->count() === 0) {
            return 1;
        }

        // 4. Calculate recursively decision children count
        $count = 1;
        foreach ($decision->getChildren() as $child) {
            $count += $this->calculateStrategyChildrenRecursively($strategy, $child);
        }

        // 5. Return total count
        return $count;
    }

    protected function calculateDecisionsDataChildrenRecursively($data)
    {
        // 1. Check is data has decisions
        if (empty($data['type'])) {
            return 0;
        }

        // 2. Stop condition - when data has no children
        if (empty($data['children'])) {
            return 1;
        }

        // 3. Calculate recursively decision children count
        $count = 1;
        foreach ($data['children'] as $child) {
            $count += $this->calculateDecisionsDataChildrenRecursively($child);
        }

        // 4. Return total count
        return $count;
    }

    protected function findNotEmptyStrategy(): ?Strategy
    {
        $repository = $this->entityManager->getRepository(Decision::class);
        /** @var Decision|null $decision */
        $decision = $repository
            ->createQueryBuilder('d')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        if ($decision === null) {
            return null;
        }

        return $decision->getStrategy();
    }

    protected function generateRandomDecisionsData($stepsLeft, $stepDecision = [], $chanceOfExtendingBranch = null)
    {
        // Stop condition - when it's no more steps left
        if ($stepsLeft <= 0) {
            return $stepDecision;
        }

        // Get services
        $decisionService = $this->getStrategyDecisionsService();
        if ($chanceOfExtendingBranch === null) {
            $chanceOfExtendingBranch = $this->getStrategyService()->getChanceOfExtendingBranch();
        }

        // Create root decision data
        if (empty($stepDecision)) {
            $stepDecision = ['type' => $decisionService->getRandomDecisionType(), 'children' => []];
        }

        // Create two data params and add them to step decision as children
        $stepsLeft--;
        for ($i = 0; $i < 2; $i++) {
            if ($this->faker->boolean($chanceOfExtendingBranch)) {
                $child = ['type' => $decisionService->getRandomDecisionType(), 'children' => []];
                $stepDecision['children'][] = $this->generateRandomDecisionsData($stepsLeft, $child, $chanceOfExtendingBranch);
            }
        }

        // Return decision
        return $stepDecision;
    }
}