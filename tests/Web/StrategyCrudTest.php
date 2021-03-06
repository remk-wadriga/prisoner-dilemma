<?php

namespace App\Tests\Web;

use App\Tests\AbstractApiTestCase;
use App\Entity\Decision;
use App\Entity\Strategy;
use App\Entity\Types\Enum\IsEnabledEnum;
use App\Entity\User;
use App\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\ApiResponse;
use Faker\Factory;

class StrategyCrudTest extends AbstractApiTestCase
{
    public function testListAction()
    {
        // 1. Get User and login him
        $this->logInAsUser();
        $user = $this->user;

        // 2. Send request
        $response = $this->request('app_homepage');
        // Check response status - mus be equals to 200
        $this->assertEquals(Response::HTTP_OK, $response->getStatus(),
            sprintf('Wrong test "get strategies list" response format, status code mus be equal to %s, but it is not. It is: %s. The content is: %s',
                Response::HTTP_OK, $response->getStatus(), $response->getContent()));
        // Check response data: it must contains the array of users strategies
        $data = $response->getData();
        $responseStrategiesCount = count($data);
        $userStrategiesCount = $user->getStrategies()->count();
        $this->assertEquals($responseStrategiesCount, $userStrategiesCount,
            sprintf('Test "get strategies list" is filed: response must contains the same count of elements, how user has strategies, it has %s count, but user has %s strategies',
                $responseStrategiesCount, $userStrategiesCount));
    }

    public function testViewAction()
    {
        // 1. Login as user
        $this->logInAsUser();
        $user = $this->user;

        // 2. Get current users strategy and send request.
        $strategy = $this->findStrategy();
        $this->assertNotEmpty($strategy, sprintf('User #%s has no strategies!', $user->getId()));

        // 3. Send response
        $response = $this->request(['strategy_show', ['id' => $strategy->getId()]]);

        // 4. Check response
        $this->checkIsCorrectStrategyParamsInResponse($response, 'show strategy');

        // 5. Get some different users strategy and send request. But if no one strategy is found - we just have nothing to test yet
        $strategy = $this->getNotUserStrategy();
        if ($strategy === null) {
            return;
        }
        $response = $this->request(['strategy_show', ['id' => $strategy->getId()]]);
        $this->checkNotOwnStrategyResponse($response, 'show another user strategy');
    }

    public function testCreateAction()
    {
        // 1. Get User and login him
        $this->logInAsUser();
        $user = $this->user;

        // 2. Create new strategy params
        $faker = Factory::create();
        $name = $faker->text(17);
        $description = $faker->text;
        $status = $faker->randomElement(IsEnabledEnum::getAvailableTypes());
        $data = $this->createStrategyDataArray($name, $description, $status);

        // 3. Try to create new strategy
        $response = $this->request('strategy_create', $data, 'POST');
        $strategy = $this->checkIsCorrectStrategyParamsInResponse($response, 'create a new strategy', $name, $description, $status, $user);
        $this->assertNotNull($strategy, 'Testing "create new strategy" is failed: where is my strategy?..');

        // 4. If we are here, it means that everything is correct, so, we can delete this test strategy
        $this->entityManager->remove($strategy);
        $this->entityManager->flush();
    }

    public function testUpdateAction()
    {
        $testKeysID = 'test_update_strategy_action';
        
        // 1. Get User and login him
        $this->logInAsUser();
        $user = $this->user;

        // 2. Get strategy and create new params for it.
        $strategy = $this->findStrategy();
        $this->assertNotEmpty($strategy, sprintf('User #%s has no strategies!', $user->getId()));

        // Remember old and new params
        $oldName = $strategy->getName();
        $oldDescription = $strategy->getDescription();
        $oldStatus = $strategy->getStatus();
        $oldDecisionsCount = $this->calculateStrategyDecisionsCount($strategy);
        $faker = Factory::create();
        $newName = $faker->text(17);
        $newDescription = $faker->text;
        $newStatus = $oldStatus == IsEnabledEnum::TYPE_ENABLED ? IsEnabledEnum::TYPE_DISABLED : IsEnabledEnum::TYPE_ENABLED;
        $data = $this->createStrategyDataArray($newName, $newDescription, $newStatus);

        // 3. Try to update strategy
        $response = $this->request(['strategy_update', ['id' => $strategy->getId()]], $data, 'PUT');
        $strategy = $this->checkIsCorrectStrategyParamsInResponse($response, 'update strategy', $newName, $newDescription, $newStatus, $user);
        $this->assertNotNull($strategy, 'Testing "update strategy" is failed: where is my strategy?..');

        // 4. Try to update strategy name
        $newName = $faker->name;
        $data = $this->createStrategyDataArray($newName);
        $response = $this->request(['strategy_update', ['id' => $strategy->getId()]], $data, 'PUT');
        $this->checkIsCorrectStrategyParamsInResponse($response, 'update strategy name');

        // 5. Try to update strategy description
        $newDescription = $faker->text;
        $data = $this->createStrategyDataArray(null, $newDescription);
        $response = $this->request(['strategy_update', ['id' => $strategy->getId()]], $data, 'PUT');
        $this->checkIsCorrectStrategyParamsInResponse($response, 'update strategy description');

        // 6. Try to update strategy status
        $newStatus = $newStatus == IsEnabledEnum::TYPE_ENABLED ? IsEnabledEnum::TYPE_DISABLED : IsEnabledEnum::TYPE_ENABLED;
        $data = $this->createStrategyDataArray(null, null, $newStatus);
        $response = $this->request(['strategy_update', ['id' => $strategy->getId()]], $data, 'PUT');
        $this->checkIsCorrectStrategyParamsInResponse($response, 'update strategy status');

        // 7. Set old strategy params back
        $data = $this->createStrategyDataArray($oldName, $oldDescription, $oldStatus);
        $response = $this->request(['strategy_update', ['id' => $strategy->getId()]], $data, 'PUT');
        // Check is response status is equals to 200
        $this->assertEquals(Response::HTTP_OK, $response->getStatus(),
            sprintf('Wrong test "Set old strategy params back" response format, status code mus be equal to %s, but it is not. It is: %s. The content is: %s',
                Response::HTTP_OK, $response->getStatus(), $response->getContent()));

        // 8. Get some different users strategy and try to update it
        $notUserStrategy = $this->getNotUserStrategy();
        if ($notUserStrategy !== null) {
            $response = $this->request(['strategy_update', ['id' => $notUserStrategy->getId()]], $data, 'PUT');
            $this->checkNotOwnStrategyResponse($response, $testKeysID);
        }
        
        // 9. Check is strategy has the same decisions count
        $newDecisionsCount = $this->calculateStrategyDecisionsCount($strategy);
        $this->assertEquals($oldDecisionsCount, $newDecisionsCount, sprintf('Test %s failed. Strategy #%s decisions count is changed after updating. Old value is %s, new value is %s',
            $testKeysID, $strategy->getId(), $oldDecisionsCount, $newDecisionsCount));
    }
    
    public function testGenerateRandomAction()
    {
        // 1. Login user
        $this->logInAsUser();
        $user = $this->user;

        // 2. Create new strategy params
        $faker = Factory::create();
        $name = $faker->text(17);
        $steps = $faker->numberBetween(1, 5);
        $data = [
            'name' => $name,
            'steps' => $steps,
            'extendingChance' => 100,
        ];

        // 3. Calculate expected strategy steps count
        $expectedDecisionsCount = pow(2, $steps + 1) - 1;

        // 4. Try to create new strategy
        $response = $this->request('strategy_generate_random', $data, 'POST');
        $strategy = $this->checkIsCorrectStrategyParamsInResponse($response, 'generate random strategy', $name, null, IsEnabledEnum::TYPE_ENABLED, $user);
        $this->assertNotNull($strategy, 'Testing "generate random strategy" is failed: where is my strategy?..');

        // 5. Check strategy decisions count
        /** @var \App\Repository\DecisionRepository $decisionsRepository */
        $decisionsRepository = $this->entityManager->getRepository(Decision::class);
        $realDecisionsCount = count($decisionsRepository->findDecisionsByStrategyIdOrderedByIdDesc($strategy->getId()));
        $this->assertEquals($expectedDecisionsCount, $realDecisionsCount,
            sprintf('Testing "generate random strategy" is failed: expected decisions count for %s steps is %s, %s given (Strategy ID: %s)',
                $steps, $expectedDecisionsCount, $realDecisionsCount, $strategy->getId()));

        // 6. If we are here, it means that everything is correct, so, we can delete this test strategy
        $this->entityManager->remove($strategy);
        $this->entityManager->flush();
    }

    public function testDeleteAction()
    {
        // 1. Get User and login him and remember user strategies count
        $this->logInAsUser();
        $user = $this->user;
        $userStrategiesCount = $user->getStrategies()->count();

        // 2. Generate new random strategy and find it it
        $data = [
            'steps' => 2,
            'extendingChance' => 100,
        ];
        $response = $this->request('strategy_generate_random', $data, 'POST');
        $data = $response->getData();
        $this->assertArrayHasKey('id', $data,
            sprintf('Test "delete strategy action" is failed: can\'t generate random strategy. Generating action response code is %s, body is %s',
            $response->getStatus(), $response->getContent()));
        $strategy = $this->findStrategy(null, $data['id']);
        $this->assertNotEmpty($strategy,
            sprintf('Test "delete strategy action" is failed: can\'t find just created strategy by ID #%s', $data['id']));

        // 3. Try to delete just created strategy
        $response = $this->request(['strategy_delete', ['id' => $strategy->getId()]], [], 'DELETE');
        // Check request
        $this->assertEquals(Response::HTTP_OK, $response->getStatus(),
            sprintf('Wrong test "delete strategy" response format, status code must be equal to %s, but it is not. It is: %s. The content is: %s',
                Response::HTTP_OK,  $response->getStatus(), $response->getContent()));
        $this->assertContains('OK', $response->getContent(),
            'Wrong test "delete strategy" response format, response must contains "OK" string, but it is not. It is: %s.', $response->getContent());
        // Check data - user mustn`t have this strategy and user`s strategies count mus be equals to "oldCount"
        $this->assertFalse($user->getStrategies()->contains($strategy), sprintf('Test "delete strategy" is failed: user #%s steel has the strategy #%s',
            $user->getId(), $strategy->getId()));
        $this->assertEquals($user->getStrategies()->count(), $userStrategiesCount,
            sprintf('Test "delete strategy" is failed: user #%s has a %s strategies, but he should have %s after deleting a new one',
                $user->getId(), $userStrategiesCount, $userStrategiesCount));

        // 3. Try do delete strategy of some different user. But if it`s no other users strategies yet, we just have nothing to test yet
        $strategy = $this->getNotUserStrategy();
        if ($strategy === null) {
            return;
        }
        $response = $this->request(['strategy_delete', ['id' => $strategy->getId()]], [], 'DELETE');
        $this->checkNotOwnStrategyResponse($response, 'delete not own strategy');
    }


    private function calculateStrategyDecisionsCount(Strategy $strategy)
    {
        return $this->entityManager->getRepository(Decision::class)
            ->createQueryBuilder('d')
            ->select('COUNT(d)')
            ->andWhere('d.strategy = :strategy')
            ->setParameter('strategy', $strategy)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function createStrategyDataArray(string $name = null, string $description = null, string $status = null)
    {
        $attributes = [];
        if ($name !== null) {
            $attributes['name'] = $name;
        }
        if ($description !== null) {
            $attributes['description'] = $description;
        }
        if ($status !== null) {
            $attributes['status'] = $status;
        }
        return ['strategy_form' => $attributes];
    }

    private function checkIsCorrectStrategyParamsInResponse(ApiResponse $response, string $testKeysID, string $name = null, string $description = null, string $status = null, User $user = null): ?Strategy
    {
        $strategy = null;
        // Check response status - mus be equals to 200
        $this->assertEquals(Response::HTTP_OK, $response->getStatus(),
            sprintf('Wrong test "%s" response format, status code mus be equal to %s, but it is not. It is: %s. The content is: %s',
                $testKeysID, Response::HTTP_OK, $response->getStatus(), $response->getContent()));
        // Check is response data has all necessary params
        $data = $response->getData();
        $this->assertArrayHasKey('id', $data,
            sprintf('Wrong test "%s" response format, response must contains the "id" param, but it is not. It is: %s', $testKeysID, $response->getContent()));
        $this->assertArrayHasKey('name', $data,
            sprintf('Wrong test "%s" response format, response must contains the "name" param, but it is not. It is: %s', $testKeysID, $response->getContent()));
        $this->assertArrayHasKey('description', $data,
            sprintf('Wrong test "%s" response format, response must contains the "description" param, but it is not. It is: %s', $testKeysID, $response->getContent()));
        $this->assertArrayHasKey('status', $data,
            sprintf('Wrong test "%s" response format, response must contains the "status" param, but it is not. It is: %s', $testKeysID, $response->getContent()));
        // Check all strategy params
        if ($name !== null) {
            $this->assertEquals($data['name'], $name,
                sprintf('Wrong test "%s" response format, the response must have an "name" param equals to "%s", but it is not, it is "%s". The response content is: %s',
                    $testKeysID, $name, $data['name'], $response->getContent()));
        }
        if ($description !== null) {
            $this->assertEquals($data['description'], $description,
                sprintf('Wrong test "%s" response format, the response must have an "description" param equals to "%s", but it is not, it is "%s". The response content is: %s',
                    $testKeysID, $description, $data['description'], $response->getContent()));
        }
        if ($status !== null) {
            $this->assertEquals($data['status'], $status,
                sprintf('Wrong test "%s" response format, the response must have an "status" param equals to "%s", but it is not, it is "%s". The response content is: %s',
                    $testKeysID, $status, $data['status'], $response->getContent()));
        }
        if ($user !== null) {
            // Check dependencies between user and this strategy
            // Find this strategy
            $strategy = $this->entityManager->getRepository(Strategy::class)->find($data['id']);
            $this->assertNotNull($strategy, sprintf('Test "%s" failed. Can`t find the strategy #%s in DB', $testKeysID, $data['id']));
            // Check is strategy has user
            $this->assertNotNull($strategy->getUser(), sprintf('Test "%s" failed. Strategy #%s has no user', $testKeysID, $data['id']));
            // Check is strategy has correct user ID
            $this->assertEquals($strategy->getUser()->getId(), $user->getId(),
                sprintf('Test "%s" failed. Strategy #%s has not correct user. Strategy user ID is %s, but must be %s',
                    $testKeysID, $data['id'], $strategy->getUser()->getId(), $user->getId()));
            // Check is user has this strategy
            $this->assertTrue($user->getStrategies()->contains($strategy), sprintf('Test "%s" failed. User #%s has not strategy #%s', $testKeysID, $user->getId(), $data['id']));
        }
        return $strategy;
    }

    private function checkNotOwnStrategyResponse(ApiResponse $response, string $testKeysID)
    {
        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatus(),
            sprintf('Wrong test "%s" response format, status code must be equal to %s, but it is not. It is: %s. The content is: %s',
                $testKeysID, Response::HTTP_FORBIDDEN, $response->getStatus(), $response->getContent()));
        $data = $response->getData();
        $this->assertArrayHasKey('error', $data,
            sprintf('Wrong test "%s" response format, response must have a "error" param but it`s not. The response is: %s', $testKeysID, $response->getContent()));
        $this->assertArrayHasKey('code', $data['error'],
            sprintf('Wrong test "%s" response format, response must have a "code" param but it`s not. The response is: %s', $testKeysID, json_encode($data['error'])));
        $this->assertArrayHasKey('message', $data['error'],
            sprintf('Wrong test "%s" response format, response must have a "message" param but it`s not. The response is: %s', $testKeysID, json_encode($data['error'])));
        $this->assertEquals(HttpException::CODE_ACCESS_DENIED, $data['error']['code'],
            sprintf('Wrong test "%s" response format, response must have a "code" param equals to %s but it`s not, It is: %s. The response is: %s',
                $testKeysID,HttpException::CODE_ACCESS_DENIED, $data['error']['code'], json_encode($data['error'])));
        $this->assertContains('access denied', strtolower($data['error']['message']),
            sprintf('Wrong test "%s" response format, response must have a "message" param equals to "%s" but it`s not, It is: "%s". The response is: %s',
                $testKeysID,'access denied', $data['error']['message'], json_encode($data['error'])));
    }

    private function findStrategy(int $userID = null, int $strategyID = null): ?Strategy
    {
        if ($userID === null && $this->user !== null) {
            $userID = $this->user->getId();
        }
        if ($userID === null) {
            return null;
        }
        
        if ($strategyID === null) {
            $criteria = ['user' => $userID];
        } else {
            $criteria = ['id' => $strategyID];
        }
        return $this->entityManager->getRepository(Strategy::class)->findOneBy($criteria);
    }

    private function getNotUserStrategy(int $userID = null): ?Strategy
    {
        if ($userID === null && $this->user !== null) {
            $userID = $this->user->getId();
        }
        if ($userID === null) {
            return null;
        }
        return $strategy = $this->entityManager->getRepository(Strategy::class)->createQueryBuilder('s')
            ->andWhere('s.user != :user')
            ->setParameter('user', $userID)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
