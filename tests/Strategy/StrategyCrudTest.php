<?php

namespace App\Tests\Strategy;

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
        $response = $this->request('strategy_create', $data, 'PUT');
        $strategy = $this->checkIsCorrectStrategyParamsInResponse($response, 'create a new strategy', $name, $description, $status, $user);
        $this->assertNotNull($strategy, 'Testing "create new strategy" is failed: where is my strategy?..');

        // 4. If we are here, it means that everything is correct, so, we can delete this test strategy
        $this->entityManager->remove($strategy);
        $this->entityManager->flush();
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
        /** @var Strategy $strategy */
        $strategy = $this->entityManager->getRepository(Strategy::class)->findOneBy(['user' => $user->getId()]);
        $this->assertNotNull($strategy, sprintf('Test "show strategy" failed: user #%s doesn`t have strategies', $user->getId()));
        $response = $this->request(['strategy_show', ['id' => $strategy->getId()]]);
        // 3. Check response
        $this->checkIsCorrectStrategyParamsInResponse($response, 'show strategy');

        // 3. Get some different users strategy and send request
        /** @var Strategy $strategy */
        $strategy = $this->entityManager->getRepository(Strategy::class)->createQueryBuilder('s')
            ->andWhere('s.user != :user')
            ->setParameter('user', $user->getId())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        $response = $this->request(['strategy_show', ['id' => $strategy->getId()]]);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatus(),
            sprintf('Wrong test "show another strategy" response format, status code must be equal to %s, but it is not. It is: %s. The content is: %s',
                Response::HTTP_NOT_FOUND, $response->getStatus(), $response->getContent()));
        $data = $response->getData();
        $this->assertArrayHasKey('error', $data,
            sprintf('Wrong test "show another user strategy" response format, response must have a "error" param but it`s not. The response is: %s', $response->getContent()));
        $this->assertArrayHasKey('code', $data['error'],
            sprintf('Wrong test "show another user strategy" response format, response must have a "code" param but it`s not. The response is: %s', json_encode($data['error'])));
        $this->assertArrayHasKey('message', $data['error'],
            sprintf('Wrong test "show another user strategy" response format, response must have a "message" param but it`s not. The response is: %s', json_encode($data['error'])));
        $this->assertEquals(HttpException::CODE_NOT_FOUND, $data['error']['code'],
            sprintf('Wrong test "show another user strategy" response format, response must have a "code" param equals to %s but it`s not, It is: %s. The response is: %s',
                HttpException::CODE_NOT_FOUND, $data['error']['code'], json_encode($data['error'])));
        $this->assertContains('not found', $data['error']['message'],
            sprintf('Wrong test "show another user strategy" response format, response must have a "message" param equals to "%s" but it`s not, It is: "%s". The response is: %s',
                'not found', $data['error']['message'], json_encode($data['error'])));
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
            $status = IsEnabledEnum::getTypeName($status);
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
}
