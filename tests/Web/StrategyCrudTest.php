<?php

namespace App\Tests\Web;

use App\Entity\Decision;
use App\Entity\Strategy;
use App\Entity\Types\Enum\IsEnabledEnum;
use App\Entity\User;
use App\Tests\AbstractApiTestCase;
use App\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;
use App\Tests\ApiResponse;
use Faker\Factory;

class StrategyCrudTest extends AbstractApiTestCase
{
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
        // 1. Get User and login him
        $this->logInAsUser();
        $user = $this->user;

        // 2. Get strategy and create new params for it
        $strategy = $this->getUserStrategy();
        $this->assertNotNull($strategy, sprintf('Test "update strategy" action failed: user #%s doesn`t have strategies', $user->getId()));
        // Remember old and new params
        $oldName = $strategy->getName();
        $oldDescription = $strategy->getDescription();
        $oldStatus = $strategy->getStatus();
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
        $strategy = $this->getNotUserStrategy();
        $response = $this->request(['strategy_update', ['id' => $strategy->getId()]], $data, 'PUT');
        $this->checkNotOwnStrategyResponse($response, 'update another user strategy');
    }

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
        // 1. Get User and login him
        $this->logInAsUser();
        $user = $this->user;

        // 2. Get current users strategy and send request
        $strategy = $this->getUserStrategy();
        $this->assertNotNull($strategy, sprintf('Test "show strategy" failed: user #%s doesn`t have strategies', $user->getId()));
        $response = $this->request(['strategy_show', ['id' => $strategy->getId()]]);
        // 3. Check response
        $this->checkIsCorrectStrategyParamsInResponse($response, 'show strategy');

        // 3. Get some different users strategy and send request
        $strategy = $this->getNotUserStrategy();
        $response = $this->request(['strategy_show', ['id' => $strategy->getId()]]);
        $this->checkNotOwnStrategyResponse($response, 'show another user strategy');

    }

    public function testDeleteAction()
    {
        // 1. Get User and login him and remember user strategies count
        $this->logInAsUser();
        $user = $this->user;
        $userStrategiesCount = $user->getStrategies()->count();

        // 2. Get some user strategy, remember it prams and try to delete it
        $strategy = $this->getUserStrategy();
        $strategyParams = [
            'name' => $strategy->getName(),
            'description' => $strategy->getDescription(),
            'status' => $strategy->getStatus(),
        ];
        $response = $this->request(['strategy_delete', ['id' => $strategy->getId()]], [], 'DELETE');
        // Check request
        $this->assertEquals(Response::HTTP_OK, $response->getStatus(),
            sprintf('Wrong test "delete strategy" response format, status code must be equal to %s, but it is not. It is: %s. The content is: %s',
                Response::HTTP_OK,  $response->getStatus(), $response->getContent()));
        $this->assertContains('OK', $response->getContent(),
            'Wrong test "delete strategy" response format, response must contains "OK" string, but it is not. It is: %s.', $response->getContent());
        // Check data - user mustn`t have this strategy and user`s strategies count mus be equals to "oldCount - 1"
        $this->assertFalse($user->getStrategies()->contains($strategy), sprintf('Test "delete strategy" is failed: user #%s steel has the strategy #%s',
            $user->getId(), $strategy->getId()));
        $this->assertEquals($user->getStrategies()->count(), $userStrategiesCount - 1,
            sprintf('Test "delete strategy" is failed: user #%s steel has a %s strategies, but he should have %s after deleting the one',
                $user->getId(), $userStrategiesCount, $userStrategiesCount - 1));

        // 3. Try do delete strategy of some different user
        $strategy = $this->getNotUserStrategy();
        $response = $this->request(['strategy_delete', ['id' => $strategy->getId()]], [], 'DELETE');
        $this->checkNotOwnStrategyResponse($response, 'delete not own strategy');

        // 4. Create the same strategy again
        $strategy = new Strategy();
        $user = $this->entityManager->getRepository(User::class)->find($user->getId());
        $strategy
            ->setUser($user)
            ->setName($strategyParams['name'])
            ->setDescription($strategyParams['description'])
            ->setStatus($strategyParams['status']);
        $this->entityManager->persist($strategy);
        $this->entityManager->flush();
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
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatus(),
            sprintf('Wrong test "show another strategy" response format, status code must be equal to %s, but it is not. It is: %s. The content is: %s',
                Response::HTTP_NOT_FOUND, $response->getStatus(), $response->getContent()));
        $data = $response->getData();
        $this->assertArrayHasKey('error', $data,
            sprintf('Wrong test "%s" response format, response must have a "error" param but it`s not. The response is: %s', $testKeysID, $response->getContent()));
        $this->assertArrayHasKey('code', $data['error'],
            sprintf('Wrong test "%s" response format, response must have a "code" param but it`s not. The response is: %s', $testKeysID, json_encode($data['error'])));
        $this->assertArrayHasKey('message', $data['error'],
            sprintf('Wrong test "%s" response format, response must have a "message" param but it`s not. The response is: %s', $testKeysID, json_encode($data['error'])));
        $this->assertEquals(HttpException::CODE_NOT_FOUND, $data['error']['code'],
            sprintf('Wrong test "%s" response format, response must have a "code" param equals to %s but it`s not, It is: %s. The response is: %s',
                $testKeysID,HttpException::CODE_NOT_FOUND, $data['error']['code'], json_encode($data['error'])));
        $this->assertContains('not found', $data['error']['message'],
            sprintf('Wrong test "%s" response format, response must have a "message" param equals to "%s" but it`s not, It is: "%s". The response is: %s',
                $testKeysID,'not found', $data['error']['message'], json_encode($data['error'])));
    }

    private function getUserStrategy(int $userID = null): ?Strategy
    {
        if ($userID === null && $this->user !== null) {
            $userID = $this->user->getId();
        }
        if ($userID === null) {
            return null;
        }
        return $this->entityManager->getRepository(Strategy::class)->findOneBy(['user' => $userID]);
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
