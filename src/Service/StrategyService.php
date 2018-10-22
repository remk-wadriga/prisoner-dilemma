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


    public function generateRandomStrategy(User $user, $steps = 0): Strategy
    {
        if ($steps === 0) {
            $steps = $this->faker->numberBetween(2, 9);
        }

        // Create strategy
        $strategy = (new Strategy())
            ->setUser($user)
            ->setName($this->faker->name . ' ' . $steps . ' steps')
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

        if ($decision->getChildren()->count() === 0) {
            $children = [
                $this->decisionsService->generateRandomDecision($decision->getStrategy(), null, DecisionTypeEnum::TYPE_ACCEPT),
                $this->decisionsService->generateRandomDecision($decision->getStrategy(), null, DecisionTypeEnum::TYPE_REFUSE),
            ];
        } else {
            $children = $decision->getChildren();
        }

        foreach ($children as $child) {
            $decision->addChild($child);
            $this->addDecisionsChildrenRecursively($child, --$stepsCount);
        }
    }
}