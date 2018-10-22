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
use App\Entity\Types\Enum\DecisionTypeEnum;

class StrategyService extends AbstractService
{
    /** @var \Faker\Generator */
    private $faker;
    private $decisionsService;

    public function __construct(StrategyDecisionsService $decisionsService)
    {
        $this->faker = Factory::create();
        $this->decisionsService = $decisionsService;
    }


    public function generateRandomStrategy(User $user, $steps = 0, $name = null): Strategy
    {
        if ($steps === 0) {
            $steps = $this->faker->numberBetween(1, 10);
        }
        if ($name === null) {
            $name = $this->faker->name . ' ' . $steps . ' steps';
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
        if ($this->faker->boolean(80)) {
            $this->addDecisionsChildrenRecursively($partnerAcceptDecision, $stepsCount);
        }
        if ($this->faker->boolean(80)) {
            $this->addDecisionsChildrenRecursively($partnerRefuseDecision, $stepsCount);
        }
    }
}