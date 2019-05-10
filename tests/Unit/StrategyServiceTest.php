<?php

namespace App\Tests\Unit;

use App\Entity\Strategy;

class StrategyServiceTest extends BaseStrategyTestCase
{
    public function testGeneratingRandomStrategy()
    {
        // 1. Try to generate random strategy
        $service = $this->getStrategyService();
        $steps = $this->faker->numberBetween(1, 5);
        $strategy = $service->generateRandomStrategy($this->getRandomUser(), $steps, null, 100);
        $this->assertNotEmpty($strategy, 'Test "StrategyService.generateRandomStrategy" is filed: function not return the strategy');

        // 2. Calculate expected strategy steps count
        $expectedDecisionsCount = pow(2, $steps + 1) - 1;

        // 3. Calculate actual strategy steps count
        $actualDecisionsCount = $this->calculateStrategyChildrenRecursively($strategy);

        // 4. Check is decisions count has correct value
        $this->assertEquals($expectedDecisionsCount, $actualDecisionsCount,
            sprintf('Test "StrategyService.generateRandomStrategy" is filed: expected count of strategy decisions is %s, actual is %s', $expectedDecisionsCount, $actualDecisionsCount));
    }

    public function testParsingDecisionsData()
    {
        // 1. Create new strategy
        $strategy = new Strategy();

        // 2. Set strategy data array
        $steps = $this->faker->numberBetween(1, 5);
        $strategy->setDecisionsData($this->generateRandomDecisionsData($steps, [], 100));

        // 3. Create decisions params array to real decisions tree and add it to strategy
        $this->getStrategyService()->parseDecisionsData($strategy);

        // 4. Calculate expected strategy steps count
        $expectedDecisionsCount = pow(2, $steps + 1) - 1;

        // 5. Calculate actual strategy steps count
        $actualDecisionsCount = $this->calculateStrategyChildrenRecursively($strategy);

        // 6. Check is decisions count has correct value
        $this->assertEquals($expectedDecisionsCount, $actualDecisionsCount,
            sprintf('Test "StrategyService.parseDecisionsData" is filed: expected count of strategy decisions is %s, actual is %s', $expectedDecisionsCount, $actualDecisionsCount));
    }
}
