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
        $user = $this->getRandomUser();

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

        // 5. Check all strategies data required params and calculate actual decisions count
        $actualDecisionsCount = 0;
        foreach ($strategiesData as $data) {
            $this->assertArrayHasKey('strategyID', $data,
                'Test "GameService.createDecisionsTreeByStrategiesIds" is filed: all strategy data must have an "strategyID" key');
            $this->assertArrayHasKey('id', $data,
                'Test "GameService.createDecisionsTreeByStrategiesIds" is filed: all strategy data must have an "id" key');
            $this->assertArrayHasKey('parentID', $data,
                'Test "GameService.createDecisionsTreeByStrategiesIds" is filed: all strategy data must have an "parentID" key');
            $this->assertArrayHasKey('type', $data,
                'Test "GameService.createDecisionsTreeByStrategiesIds" is filed: all strategy data must have an "type" key');
            $this->assertArrayHasKey('level', $data,
                'Test "GameService.createDecisionsTreeByStrategiesIds" is filed: all strategy data must have an "level" key');
            $this->assertArrayHasKey('children', $data,
                'Test "GameService.createDecisionsTreeByStrategiesIds" is filed: all strategy data must have an "children" key');

            $this->assertInternalType('integer', $data['strategyID'], sprintf(
                'Test "GameService.createDecisionsTreeByStrategiesIds" is filed: pram "strategyID" must have an "integer" type, but "%s" given',
                gettype($data['strategyID'])
            ));
            $this->assertInternalType('integer', $data['id'], sprintf(
                'Test "GameService.createDecisionsTreeByStrategiesIds" is filed: pram "id" must have an "integer" type, but "%s" given',
                gettype($data['id'])
            ));
            if (!is_null($data['parentID'])) {
                $this->assertInternalType('integer', $data['parentID'], sprintf(
                    'Test "GameService.createDecisionsTreeByStrategiesIds" is filed: pram "parentID" must have an "integer" type, but "%s" given',
                    gettype($data['parentID'])
                ));
            }
            $this->assertInternalType('string', $data['type'], sprintf(
                'Test "GameService.createDecisionsTreeByStrategiesIds" is filed: pram "type" must have an "string" type, but "%s" given',
                gettype($data['type'])
            ));
            $this->assertInternalType('integer', $data['level'], sprintf(
                'Test "GameService.createDecisionsTreeByStrategiesIds" is filed: pram "level" must have an "integer" type, but "%s" given',
                gettype($data['level'])
            ));
            $this->assertInternalType('array', $data['children'], sprintf(
                'Test "GameService.createDecisionsTreeByStrategiesIds" is filed: pram "children" must have an "array" type, but "%s" given',
                gettype($data['children'])
            ));

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