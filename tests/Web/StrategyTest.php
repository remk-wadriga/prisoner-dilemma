<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 27.10.2018
 * Time: 21:55
 */

namespace App\Tests\Web;

use App\Entity\Strategy;
use App\Entity\Types\Enum\IsEnabledEnum;
use App\Exception\GameException;
use App\Tests\ApiResponse;
use App\Tests\AbstractApiTestCase;
use Faker\Factory;
use Symfony\Component\HttpFoundation\Response;

class StrategyTest extends AbstractApiTestCase
{
    public function testGame()
    {
        // 1. Login as standard user
        $this->logInAsUser();

        // 2. Find some strategies for game
        $faker = Factory::create();
        /** @var \App\Repository\StrategyRepository $strategyRepository */
        $strategyRepository = $this->entityManager->getRepository(Strategy::class);
        /** @var \App\Entity\Strategy[] $strategies */
        $strategies = $strategyRepository->createQueryBuilder('s')
            ->andWhere('s.user = :user')
            ->andWhere('s.status = :status_enabled')
            ->setMaxResults($faker->numberBetween(2, 7))
            ->setParameter('status_enabled', IsEnabledEnum::TYPE_ENABLED)
            ->setParameter('user', $this->user)
            ->getQuery()
            ->getResult()
        ;
        // If there are no strategies in DB - we have nothing to test
        if (empty($strategies)) {
            return;
        }
        $strategiesIds = [];
        foreach ($strategies as $strategy) {
            $strategiesIds[] = $strategy->getId();
        }
        // 3. Send "/game/start" without calculating individual results query and check the response result
        $response = $this->request('game_start', ['strategiesIds' => $strategiesIds, 'writeIndividualResults' => false], 'POST');
        $this->checkStartGameResponse($response, false, count($strategiesIds));

        // 4. Send "/game/start" with calculating individual results query and check the response result
        $response = $this->request('game_start', ['strategiesIds' => $strategiesIds, 'writeIndividualResults' => true], 'POST');
        $this->checkStartGameResponse($response, true, count($strategiesIds));

        // 5. Send request with 1 strategy and check the error response
        $response = $this->request('game_start', ['strategiesIds' => [current($strategiesIds)]], 'POST');
        $this->checkStartGameResponse($response, true, 0, Response::HTTP_BAD_REQUEST, GameException::CODE_GAME_IMPOSSIBLE);

        // 6. Send request with incorrect strategy IDs and check the error response
        $response = $this->request('game_start', ['strategiesIds' => ['incorrect_id_1', 'incorrect_id_2']], 'POST');
        $this->checkStartGameResponse($response, true, 0, Response::HTTP_BAD_REQUEST, GameException::CODE_STRATEGIES_NOT_FOUND);
    }


    private function checkStartGameResponse(ApiResponse $response, bool $writeIndividualResults, int $strategiesCount, int $expectedStatus = 0, int $expectedCode = 0)
    {
        if ($expectedStatus === 0) {
            $expectedStatus = Response::HTTP_OK;
        }
        $message = 'Testing "Start game request" is failed';

        $this->assertEquals($expectedStatus, $response->getStatus(),
            sprintf('%s. The response code must be an %s, but given code is %s. Response content is %s',
                $message, $expectedStatus, $response->getStatus(), $response->getContent()));


        $mustHaveParamMessage = $message . '. The response must have a param "%s", but it hasn\'t it. The response content is %s';
        if ($expectedStatus === Response::HTTP_OK) {
            $this->assertArrayHasKey('params', $response->getData(), sprintf($mustHaveParamMessage, 'params', $response->getContent()));
            $this->assertArrayHasKey('results', $response->getData(), sprintf($mustHaveParamMessage, 'params', $response->getContent()));
            $data = $response->getData();
            $results = $data['results'];
            $jsonResults = json_encode($results);
            $mustHaveParamMessage = str_replace('The response content is', 'The response content params array is', $mustHaveParamMessage);
            $this->assertArrayHasKey('sum', $results, sprintf($mustHaveParamMessage, 'results[sum]', $jsonResults));
            $this->assertArrayHasKey('total', $results, sprintf($mustHaveParamMessage, 'results[total]', $jsonResults));
            $this->assertArrayHasKey('individual', $results, sprintf($mustHaveParamMessage, 'results[individual]', $jsonResults));

            $typeMustBeMessage = $message . '. The "%s" param must be an "%s". The response content param value is %s';
            $this->assertInternalType('integer', $results['sum'], sprintf($typeMustBeMessage, 'results[sum]', 'integer', $results['sum']));
            $this->assertInternalType('array', $results['total'], sprintf($typeMustBeMessage, 'results[total]', 'array', json_encode($results['total'])));
            $this->assertInternalType('array', $results['individual'], sprintf($typeMustBeMessage, 'results[individual]', 'array', json_encode($results['individual'])));

            $expectedIndividualResultsCount = $writeIndividualResults ? $strategiesCount : 0;
            $countMustBeMessage = $message . '. The response param "%s" count must be %s but %s given. The response param value is %s';
            $this->assertEquals($strategiesCount, count($results['total']), sprintf($countMustBeMessage,
                'results[total]',
                $strategiesCount,
                count($results['total']),
                json_encode($results['total'])));
            $this->assertEquals($expectedIndividualResultsCount, count($results['individual']), sprintf($countMustBeMessage,
                'results[individual]',
                $expectedIndividualResultsCount,
                count($results['individual']),
                json_encode($results['individual'])));

            $totalSum = 0;
            $strategiesResults = [];
            $mustHaveLeyMessage = $message . '. Total results arrays must have a "%s" key, but it\'s not. Total results is %s';
            $incorrectParamTypeMessage = $message . '. Total results arrays param "%s" myst have "%s" type but it\'s not. Param value is %s';
            $totalResultsJson = json_encode($results['total']);
            foreach ($results['total'] as $result) {
                $this->assertArrayHasKey('id', $result, sprintf($mustHaveLeyMessage, 'id', $totalResultsJson));
                $this->assertArrayHasKey('name', $result, sprintf($mustHaveLeyMessage, 'name', $totalResultsJson));
                $this->assertArrayHasKey('result', $result, sprintf($mustHaveLeyMessage, 'result', $totalResultsJson));

                $this->assertInternalType('integer', $result['id'], sprintf($incorrectParamTypeMessage, 'total[id]', 'integer', $result['id']));
                $this->assertInternalType('string', $result['name'], sprintf($incorrectParamTypeMessage, 'total[name]', 'string', $result['name']));
                $this->assertInternalType('integer', $result['result'], sprintf($incorrectParamTypeMessage, 'total[result]', 'integer', $result['result']));


                $strategiesResults[$result['id']] = $result['result'];
                $totalSum += $result['result'];
            }
            $this->assertEquals($results['sum'], $totalSum, sprintf(
                '%s. Total sum and sum of results are not much. Total sum is %s, results sum is %s. The results array is %s',
                    $message, $results['sum'], $totalSum, $totalResultsJson));

            $individualResults = [];
            $partnersResults = [];
            $partnersCount = $strategiesCount - 1;
            $mustHaveLeyMessage = str_replace('Total results', 'Individual results', $mustHaveLeyMessage);
            $incorrectParamTypeMessage = str_replace('Total results', 'Individual results', $incorrectParamTypeMessage);
            foreach ($results['individual'] as $id => $result) {
                $this->assertArrayHasKey($id, $strategiesResults,
                    sprintf('%s. Incorrect individual result ID: %s. It\'s not exists in total results array. The total array is %s',
                        $message, $id, $totalResultsJson));

                $this->assertEquals($partnersCount, count($result), sprintf($countMustBeMessage,
                    "results[individual][{$id}]",
                    $partnersCount,
                    count($result),
                    json_encode($result)));

                foreach ($result as $index => $res) {
                    $resJson = json_encode($res);
                    $this->assertArrayHasKey('result', $res, sprintf($mustHaveLeyMessage, 'result', $resJson));
                    $this->assertArrayHasKey('partnerResult', $res, sprintf($mustHaveLeyMessage, 'partnerResult', $resJson));
                    $this->assertArrayHasKey('partnerID', $res, sprintf($mustHaveLeyMessage, 'partnerID', $resJson));
                    $this->assertArrayHasKey('partnerName', $res, sprintf($mustHaveLeyMessage, 'partnerName', $resJson));

                    $this->assertInternalType('integer', $res['result'],
                        sprintf($incorrectParamTypeMessage, "results[individual][{$id}][{$index}][result]", 'integer', $res['result']));
                    $this->assertInternalType('integer', $res['partnerResult'],
                        sprintf($incorrectParamTypeMessage, "results[individual][{$id}][{$index}][partnerResult]", 'integer', $res['partnerResult']));
                    $this->assertInternalType('integer', $res['partnerID'],
                        sprintf($incorrectParamTypeMessage, "results[individual][{$id}][{$index}][partnerID]", 'integer', $res['partnerID']));
                    $this->assertInternalType('string', $res['partnerName'],
                        sprintf($incorrectParamTypeMessage, "results[individual][{$id}][{$index}][partnerName]", 'string', $res['partnerName']));

                    $this->assertArrayHasKey($res['partnerID'], $strategiesResults,
                        sprintf('%s. Incorrect individual result partner ID: %s. It\'s not exists in total results array. The total array is %s',
                            $message, $res['partnerID'], $totalResultsJson));

                    if (!isset($individualResults[$id])) {
                        $individualResults[$id] = 0;
                    }
                    if (!isset($partnersResults[$res['partnerID']])) {
                        $partnersResults[$res['partnerID']] = 0;
                    }

                    $individualResults[$id] += $res['result'];
                    $partnersResults[$res['partnerID']] += $res['partnerResult'];
                }
            }

            if ($writeIndividualResults) {
                foreach ($strategiesResults as $strategyID => $strategiesResult) {
                    foreach ($individualResults as $individualID => $individualResult) {
                        if ($strategyID !== $individualID) {
                            continue;
                        }
                        $this->assertEquals($strategiesResult, $individualResult,
                            sprintf('%s. Strategy #%s has result %s, but the same strategy in individual results has result %s. Results array is %s',
                                $message, $strategyID, $strategiesResult, $individualResult, $jsonResults));
                    }
                    foreach ($partnersResults as $partnersID => $partnersResult) {
                        if ($strategyID !== $partnersID) {
                            continue;
                        }
                        $this->assertEquals($strategiesResult, $partnersResult,
                            sprintf('%s. Strategy #%s has result %s, but the same strategy in individual partner results has result %s. Results array is %s',
                                $message, $strategyID, $strategiesResult, $partnersResult, $jsonResults));
                    }
                }
                foreach ($individualResults as $individualID => $individualResult) {
                    foreach ($partnersResults as $partnersID => $partnersResult) {
                        if ($individualID !== $partnersID) {
                            continue;
                        }
                        $this->assertEquals($individualResult, $partnersResult,
                            sprintf('%s. Individual strategy #%s has result %s, but the same strategy in individual partner results has result %s. Results array is %s',
                                $message, $individualID, $individualResult, $partnersResult, $jsonResults));
                    }
                }
            }

        } else {
            $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatus(),
                sprintf('%s. Error response code mus be %s but %s given. Error response content is %s',
                    $message, Response::HTTP_BAD_REQUEST, $response->getStatus(), $response->getContent()));
            $data = $response->getData();
            $this->assertInternalType('array', $data,
                sprintf('%s. Error response content mus be an array but it\'s not. Error response content is %s',
                    $message, $response->getContent()));

            $jsonData = json_encode($data);
            $errorJsonData = json_encode($data['error']);
            $mustHaveParamMessage = $message . '. Error response must have a param "%s", but it hasn\'t it. Error response content is %s';
            $this->assertArrayHasKey('error', $data, sprintf($mustHaveParamMessage, 'error', $jsonData));
            $this->assertArrayHasKey('code', $data['error'], sprintf($mustHaveParamMessage, 'error[code]', $errorJsonData));
            $this->assertArrayHasKey('message', $data['error'], sprintf($mustHaveParamMessage, 'error[message]', $errorJsonData));

            $this->assertEquals($expectedCode, $data['error']['code'],
                sprintf('%s. Error response code mus be %s but %s given. Error response content is %s', $message, $expectedCode, $data['error']['code'], $errorJsonData));
            $this->assertContains('Game is failed:', $data['error']['message'],
                sprintf('%s. Error response message must contains the "The game is failed:" substring, but it\'s not. Error response message message is %s',
                    $message, $data['error']['message']));
        }
    }
}