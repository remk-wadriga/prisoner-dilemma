<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 24.10.2018
 * Time: 00:47
 */

namespace App\Tests\Unit;

use App\Entity\Strategy;
use App\Service\GameResultsService;
use App\Service\GameService;
use App\Entity\Types\Enum\IsEnabledEnum;

class GameServiceTest extends BaseStrategyTestCase
{
    /** @var GameService */
    protected $gameService;
    /** @var GameResultsService */
    protected $gameResultsService;

    public function testCreatingDecisionsTreeByStrategiesIds()
    {
        // GameService.createDecisionsTreeByStrategiesIds

        // 1. Get user
        $user = $this->getUser();

        // 2. Get user enabled strategies. If no one is found, we have nothing to test yet
        /** @var \App\Repository\StrategyRepository $strategiesRepository */
        $strategiesRepository = $this->entityManager->getRepository(Strategy::class);
        /** @var Strategy[] $strategies */
        $strategies = $strategiesRepository->findBy(['user' => $user, 'status' => IsEnabledEnum::TYPE_ENABLED]);
        if (empty($strategies)) {
            return;
        }

        // 3. Calculate expected decisions count and collect strategies IDs in array
        $expectedDecisionsCount = 0;
        $strategiesIds = [];
        foreach ($strategies as $strategy) {
            $expectedDecisionsCount += $strategy->getDecisions()->count();
            $strategiesIds[] = $strategy->getId();
        }

        // 4. Create decisions data array
        $strategiesData = $this->getGameService()->createDecisionsTreeByStrategiesIds($user, $strategiesIds);
        $this->assertNotEmpty($strategiesData,
            sprintf('Test "GameService.createDecisionsTreeByStrategiesIds" is filed: data for strategies #[%s] is empty',
                implode(',', $strategiesIds)));

        // 5. Calculate actual decisions count
        $actualDecisionsCount = 0;
        foreach ($strategiesData as $data) {
            $this->assertArrayHasKey('type', $data,
                'Test "GameService.createDecisionsTreeByStrategiesIds" is filed: all strategy data mus have an "type" key');
            $this->assertArrayHasKey('children', $data,
                'Test "GameService.createDecisionsTreeByStrategiesIds" is filed: all strategy data mus have an "children" key');
            $actualDecisionsCount += $this->calculateDecisionsDataChildrenRecursively($data);
        }

        // 6. Check is decisions count has correct values
        $this->assertEquals($expectedDecisionsCount, $actualDecisionsCount,
            sprintf('Test "GameService.createDecisionsTreeByStrategiesIds" is filed: expected count of strategy decisions for strategies #[%s] is %s, actual is %s',
                implode(',', $strategiesIds), $expectedDecisionsCount, $actualDecisionsCount));
    }

    protected function getGameService(): GameService
    {
        if ($this->gameService !== null) {
            return $this->gameService;
        }
        return $this->gameService = new GameService($this->entityManager, $this->getStrategyDecisionsService(), $this->getGameResultsService(), self::$kernel->getContainer());
    }

    protected function getGameResultsService(): GameResultsService
    {
        if ($this->gameResultsService !== null) {
            return $this->gameResultsService;
        }
        return $this->gameResultsService = new GameResultsService($this->entityManager, self::$kernel->getContainer());
    }
}