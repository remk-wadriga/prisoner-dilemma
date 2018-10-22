<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 22.10.2018
 * Time: 13:11
 */

namespace App\Service;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Strategy;
use App\Entity\Decision;

class StrategyService extends AbstractService
{
    /** @var \Faker\Generator */
    private $faker;
    private $decisionsService;
    private $maxRandomDecisionsCount = 10;
    private $chanceOfExtendingBranch = 70;

    public function __construct(StrategyDecisionsService $decisionsService)
    {
        $this->faker = Factory::create();
        $this->decisionsService = $decisionsService;
    }

    /**
     * @param User $user
     * @param int $steps
     * @param string|null $name
     * @param int $chanceOfExtendingBranch
     * @return Strategy
     */
    public function generateRandomStrategy(User $user, $steps = 0, $name = null, $chanceOfExtendingBranch = null): Strategy
    {
        if (!$steps) {
            $steps = $this->faker->numberBetween(1, $this->maxRandomDecisionsCount);
        }
        if (!$name) {
            $name = $this->faker->name . ' ' . $steps . ' steps';
        }
        if ((int)$chanceOfExtendingBranch > 0) {
            $this->chanceOfExtendingBranch = (int)$chanceOfExtendingBranch;
        }

        // Create strategy
        $strategy = (new Strategy())
            ->setUser($user)
            ->setName($name)
            ->setDescription($this->faker->text)
        ;

        // Create decisions tree
        $rootDecision = $this->decisionsService->generateRandomDecision($strategy);
        $this->addDecisionsChildrenRecursively($rootDecision, $steps);

        // Add decisions tree to strategy
        $strategy->addDecision($rootDecision);

        return $strategy;
    }

    /**
     * @param Decision $decision
     * @param int $stepsCount
     */
    private function addDecisionsChildrenRecursively(Decision $decision, $stepsCount = 0)
    {
        if ($stepsCount <= 0) {
            return;
        }

        // Create 2 decisions: for both partner decisions
        $partnerAcceptDecision = $this->decisionsService->generateRandomDecision($decision->getStrategy());
        $partnerRefuseDecision = $this->decisionsService->generateRandomDecision($decision->getStrategy());

        $decision->addChild($partnerAcceptDecision);
        $decision->addChild($partnerRefuseDecision);

        // Extends some branches
        $stepsCount--;
        if ($this->faker->boolean($this->chanceOfExtendingBranch)) {
            $this->addDecisionsChildrenRecursively($partnerAcceptDecision, $stepsCount);
        }
        if ($this->faker->boolean($this->chanceOfExtendingBranch)) {
            $this->addDecisionsChildrenRecursively($partnerRefuseDecision, $stepsCount);
        }
    }
}