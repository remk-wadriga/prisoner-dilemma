<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 23.10.2018
 * Time: 14:22
 */

namespace App\Tests\Unit;

use App\Entity\Decision;

class StrategyDecisionsServiceTest extends BaseStrategyTestCase
{
    public function testParsingDecisionsData()
    {
        // 1. Search some strategy with decisions. If it's not found - we have nothing to test yet
        $strategy = $this->findNotEmptyStrategy();
        if (empty($strategy)) {
            return;
        }

        // 2. Get strategy decisions as array and check it's params
        $data = $this->getStrategyDecisionsService()->createDecisionsDataArray($strategy);
        $this->assertNotEmpty($data, 'Test "StrategyDecisionsService.parseDecisionsData" is filed: parsed decisions data is empty');
        $this->assertArrayHasKey('type', $data, 'Test "StrategyDecisionsService.parseDecisionsData" is filed: parsed decisions has no "type" attribute');
        $this->assertArrayHasKey('children', $data, 'Test "StrategyDecisionsService.parseDecisionsData" is filed: parsed decisions has no "children" attribute');

        // 3. Get expected strategy steps count
        /** @var \App\Repository\DecisionRepository  $decisionsRepository */
        $decisionsRepository = $this->entityManager->getRepository(Decision::class);
        $expectedDecisionsCount = count($decisionsRepository->findDecisionsByStrategyIdOrderedByIdDesc($strategy->getId()));

        // 4. Calculate actual strategy steps count
        $actualDecisionsCount = $this->calculateDecisionsDataChildrenRecursively($data);

        // 5. Check is decisions count has correct value
        $this->assertEquals($expectedDecisionsCount, $actualDecisionsCount,
            sprintf('Test "StrategyDecisionsService.parseDecisionsData" is filed: expected count of decisions is %s, actual is %s', $expectedDecisionsCount, $actualDecisionsCount));
    }
}