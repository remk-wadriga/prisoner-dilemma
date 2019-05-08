<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 31.10.2018
 * Time: 10:31
 */

namespace App\Tests\Web;

use App\Entity\Game;
use App\Entity\GameResult;
use App\Entity\IndividualGameResult;
use App\Entity\Strategy;
use App\Entity\Types\Enum\IsEnabledEnum;
use App\Exception\HttpException;
use App\Service\GameResultsService;
use App\Service\GameService;
use App\Service\StrategyDecisionsService;
use App\Tests\AbstractApiTestCase;
use App\Tests\ApiResponse;
use Faker\Factory;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class GameCrudTest extends AbstractApiTestCase
{
    /** @var GameService */
    private $gameService;
    /** @var StrategyDecisionsService */
    private $strategyDecisionsService;
    /** @var GameResultsService */
    private $gameResultsService;

    public function testListAction()
    {
        // Login as default user
        $this->logInAsUser();

        // Try make "GET /games" request and check response
        $response = $this->request('game_list');
        $baseMessage = 'Wrong test "Get games list" response. ';
        $this->assertEquals(Response::HTTP_OK, $response->getStatus(),
            sprintf('%sThe response mus have status %s but it\'s not. It has status %s. Response content: %s',
                $baseMessage, Response::HTTP_OK, $response->getStatus(), $response->getContent()));
        $this->assertInternalType('array', $response->getData(),
            sprintf('%sThe response content must have an array type, but it\'s not. Response content is %s',
                $baseMessage, $response->getContent()));
        // Check each game element from response
        foreach ($response->getData() as $gameData) {
            $this->checkGameInfoFromResponse($gameData, $baseMessage, [['decisionsCount', 'integer']]);
        }
    }

    public function testViewAction()
    {
        // 1. Login as default user
        $this->logInAsUser();

        // 2. Find a game
        $game = $this->findGame($this->user->getId());
        if ($game === null) {
            return;
        }

        // 3. Send the correct request and check the response
        $response = $this->request(['game_show', ['id' => $game->getId()]]);
        $this->checkStrategyRequestResponse($response, 'Show game action', Response::HTTP_OK, null, []);

        // 4. Try to get strategy by incorrect ID
        $response = $this->request(['game_show', ['id' => 'some_incorrect_id']]);
        $this->checkStrategyRequestResponse($response, 'Show game by incorrect ID action', Response::HTTP_NOT_FOUND, HttpException::CODE_NOT_FOUND, 'not found');

        // 5. Find another user game and try to get it by request
        $game = $this->findNotUserGame($this->user->getId());
        if ($game === null) {
            return;
        }
        $response = $this->request(['game_show', ['id' => $game->getId()]]);
        $this->checkStrategyRequestResponse($response, 'Show different user game action', Response::HTTP_FORBIDDEN, HttpException::CODE_ACCESS_DENIED, 'access denied');
    }

    public function testCreateAction()
    {
        // 1. Login as default user
        $this->logInAsUser();

        // 2. Get some strategies Ids and game params for game and remember it
        $faker = Factory::create();
        $strategiesIDs = $this->getUserStrategiesIds();
        $strategiesCount = count($strategiesIDs);
        $name = $faker->name;
        $description = $faker->text;
        // 2.1. Game config params
        $rounds = $faker->numberBetween(10, 30);
        $balesForWin = $faker->numberBetween(10, 30);
        $balesForLoos = $faker->numberBetween(-20, 0);
        $balesForCooperation = $faker->numberBetween(0, 15);
        $balesForDraw = $faker->numberBetween(5, 25);

        // 3. Play game to get real game results
        $gameResults = $this->getGameService()->runGame($this->user, $strategiesIDs, $rounds, $balesForWin, $balesForLoos, $balesForCooperation, $balesForDraw);

        // 4. Create game params
        $gameParams = $this->createGameParams($strategiesIDs, $gameResults, $name, $description, $rounds, $balesForWin, $balesForLoos, $balesForCooperation, $balesForDraw);

        // 5. Try to create new game
        $response = $this->request('game_create', $gameParams, 'POST');
        $game = $this->checkIsCorrectGameParamsInResponse($response, 'create a new game', $gameResults['sum'], $name, $description, $rounds, $balesForWin, $balesForLoos, $balesForCooperation, $balesForDraw, $this->user);
        $this->assertNotNull($game, 'Testing "create a new game" is failed: where is my game?..');

        // 6. Check is correct game results
        $this->checkIsCorrectGameResults($game, $strategiesCount, $gameResults, 'create a new game');

        // 7. Delete just created game
        $this->entityManager->remove($game);
        $this->entityManager->flush();
    }

    public function testUpdateAction()
    {
        $testKeysID = 'test_update_game_action';
        
        // 1. Login as default user
        $this->logInAsUser();
        $user = $this->user;

        // 2. Find a game
        $game = $this->findGame($this->user->getId());
        $this->assertNotEmpty($game, sprintf('User #%s has no games!', $user->getId()));

        // 3. Get some strategies Ids and game params for game and remember it
        $faker = Factory::create();
        $strategiesIDs = $this->getUserStrategiesIds();
        $strategiesCount = count($strategiesIDs);
        $name = $faker->name;
        $description = $faker->text;
        // 3.1. Game config params
        $rounds = $faker->numberBetween(10, 30);
        $balesForWin = $faker->numberBetween(10, 30);
        $balesForLoos = $faker->numberBetween(-20, 0);
        $balesForCooperation = $faker->numberBetween(0, 15);
        $balesForDraw = $faker->numberBetween(5, 25);

        // 4. Play game to get real game results
        $gameResults = $this->getGameService()->runGame($user, $strategiesIDs, $rounds, $balesForWin, $balesForLoos, $balesForCooperation, $balesForDraw);

        // 5. Create game params
        $gameParams = $this->createGameParams($strategiesIDs, $gameResults, $name, $description, $rounds, $balesForWin, $balesForLoos, $balesForCooperation, $balesForDraw);

        // 6. Try to update game
        $response = $this->request(['game_update', ['id' => $game->getId()]], $gameParams, 'PUT');
        $game = $this->checkIsCorrectGameParamsInResponse($response, $testKeysID, $gameResults['sum'], $name, $description, $rounds, $balesForWin, $balesForLoos, $balesForCooperation, $balesForDraw, $this->user);

        // 7. Check is correct game results
        $this->checkIsCorrectGameResults($game, $strategiesCount, $gameResults, $testKeysID);

        // 8. Remember game params
        $oldName = $game->getName();
        $oldDescription = $game->getDescription();
        $oldRoundsCount = $game->getRounds();
        $oldBalesForWin = $game->getBalesForWin();
        $oldResultsCount = $this->calculateGameResultsCount($game);
        $oldResultsSum = $this->calculateGameSesultsSum($game);
        $oldIndividualResultsCount = $this->calculateGameIndividualResultsCount($game);
        $oldIndividualResultsSum = $this->calculateGameIndividualResultsSum($game);

        // 9. Try to update game name
        $newName = $faker->name;
        $defaultGameParams = [
            'rounds' => $game->getRounds(),
            'balesForWin' => $game->getBalesForWin(),
            'balesForLoos' => $game->getBalesForLoos(),
            'balesForCooperation' => $game->getBalesForCooperation(),
            'balesForDraw' => $game->getBalesForDraw(),
        ];
        $gameParams = ['game_form' => array_merge($defaultGameParams, ['name' => $newName])];
        $response = $this->request(['game_update', ['id' => $game->getId()]], $gameParams, 'PUT');
        $game = $this->checkIsCorrectGameParamsInResponse($response, $testKeysID, null, $newName);
        $this->assertEquals($newName, $game->getName(), sprintf('Test "%s" failed. Incorrect game #%s name. Game name must be "%s" but "%s" given', $testKeysID, $game->getId(), $newName, $game->getName()));
        $this->assertNotEquals($game->getName(), $oldName, sprintf('Test "%s" failed. Incorrect game #%s name. Game name must be changed to "%s", but it\'s steel "%s"', $testKeysID, $game->getId(), $game->getName(), $oldName));

        // 10. Try to update game description
        $newDescription = $faker->text;
        $gameParams = ['game_form' => array_merge($defaultGameParams, ['name' => $newName, 'description' => $newDescription])];
        $response = $this->request(['game_update', ['id' => $game->getId()]], $gameParams, 'PUT');
        $game = $this->checkIsCorrectGameParamsInResponse($response, $testKeysID, null, null, $newDescription);
        $this->assertEquals($newDescription, $game->getDescription(), sprintf('Test "%s" failed. Incorrect game #%s description. Game description must be "%s" but "%s" given', $testKeysID, $game->getId(), $newDescription, $game->getDescription()));
        $this->assertNotEquals($game->getDescription(), $oldDescription, sprintf('Test "%s" failed. Incorrect game #%s name. Game name must be changed to "%s", but it\'s steel "%s"', $testKeysID, $game->getId(), $game->getDescription(), $oldDescription));

        // 11. Try to update game rounds count
        $newRoundsCount = $oldRoundsCount + $faker->numberBetween(1, 10);
        $gameParams = ['game_form' => array_merge($defaultGameParams, ['name' => $newName, 'rounds' => $newRoundsCount])];
        $response = $this->request(['game_update', ['id' => $game->getId()]], $gameParams, 'PUT');
        $game = $this->checkIsCorrectGameParamsInResponse($response, $testKeysID, null, null, null, $newRoundsCount);
        $this->assertEquals($newRoundsCount, $game->getRounds(), sprintf('Test "%s" failed. Incorrect game #%s roundsCount. Game roundsCount must be %s but %s given', $testKeysID, $game->getId(), $newRoundsCount, $game->getRounds()));
        $this->assertNotEquals($game->getRounds(), $oldRoundsCount, sprintf('Test "%s" failed. Incorrect game #%s roundsCount. Game roundsCount must be changed to %s, but it\'s steel %s', $testKeysID, $game->getId(), $game->getRounds(), $oldRoundsCount));

        // 12. Try to update game bales for win
        $newBalesForWin = $oldBalesForWin + $faker->numberBetween(1, 10);
        $gameParams = ['game_form' => array_merge($defaultGameParams, ['name' => $newName, 'balesForWin' => $newBalesForWin])];
        $response = $this->request(['game_update', ['id' => $game->getId()]], $gameParams, 'PUT');
        $game = $this->checkIsCorrectGameParamsInResponse($response, $testKeysID, null, null, null, null, $newBalesForWin);
        $this->assertEquals($newBalesForWin, $game->getBalesForWin(), sprintf('Test "%s" failed. Incorrect game #%s roundsCount. Game roundsCount must be %s but %s given', $testKeysID, $game->getId(), $newBalesForWin, $game->getBalesForWin()));
        $this->assertNotEquals($game->getBalesForWin(), $oldBalesForWin, sprintf('Test "%s" failed. Incorrect game #%s roundsCount. Game roundsCount must be changed to %s, but it\'s steel %s', $testKeysID, $game->getId(), $game->getBalesForWin(), $oldBalesForWin));

        // 9. Try to get game by incorrect ID
        $response = $this->request(['game_show', ['id' => 'some_incorrect_id']]);
        $this->checkStrategyRequestResponse($response, 'Updating game by incorrect ID action', Response::HTTP_NOT_FOUND, HttpException::CODE_NOT_FOUND, 'not found');

        // 10. Find another user game and try to get it by request
        $notUserGame = $this->findNotUserGame($this->user->getId());
        if ($notUserGame !== null) {
            $response = $this->request(['game_show', ['id' => $notUserGame->getId()]]);
            $this->checkStrategyRequestResponse($response, 'Updating different user\'s game action', Response::HTTP_FORBIDDEN, HttpException::CODE_ACCESS_DENIED, 'access denied');
        }

        // 11. Check is game has the same decisions and individual decisions counts
        $newResultsCount = $this->calculateGameResultsCount($game);
        $newResultsSum = $this->calculateGameSesultsSum($game);
        $newIndividualResultsCount = $this->calculateGameIndividualResultsCount($game);
        $newIndividualResultsSum = $this->calculateGameIndividualResultsSum($game);

        $this->assertEquals($oldResultsCount, $newResultsCount, sprintf('Test %s failed. Game #%s results count is changed after updating. Old value is %s, new value is %s',
            $testKeysID, $game->getId(), $oldResultsCount, $newResultsCount));
        $this->assertEquals($oldResultsSum, $newResultsSum, sprintf('Test %s failed. Game #%s results sum is changed after updating. Old value is %s, new value is %s',
            $testKeysID, $game->getId(), $oldResultsSum, $newResultsSum));
        $this->assertEquals($oldIndividualResultsCount, $newIndividualResultsCount, sprintf('Test %s failed. Game #%s individual results count is changed after updating. Old value is %s, new value is %s',
            $testKeysID, $game->getId(), $oldIndividualResultsCount, $newIndividualResultsCount));
        $this->assertEquals($oldIndividualResultsSum, $newIndividualResultsSum, sprintf('Test %s failed. Game #%s individual results sum is changed after updating. Old value is %s, new value is %s',
            $testKeysID, $game->getId(), $oldIndividualResultsSum, $newIndividualResultsSum));
    }

    public function testDeleteAction()
    {
        // 1. Login as default user
        $this->logInAsUser();

        // 2. Create a new game
        $gameParams = $this->createGameParams();
        $response = $this->request('game_create', $gameParams, 'POST');

        // 3. Try to delete just created game and check the response
        $data = $response->getData();
        $id = $data['info']['id'];
        $response = $this->request(['game_delete', ['id' => $id]], [], 'DELETE');
        $this->checkStrategyRequestResponse($response, "Deleting game #{$id}", Response::HTTP_OK, null, '"OK"');

        // 8. Try to get strategy by the same ID
        $response = $this->request(['game_delete', ['id' => $id]], [], 'DELETE');
        $this->checkStrategyRequestResponse($response, 'Deleting game by incorrect ID action', Response::HTTP_NOT_FOUND, HttpException::CODE_NOT_FOUND, 'not found');

        // 9. Find another user game and try to delete it by request
        $game = $this->findNotUserGame($this->user->getId());
        if ($game === null) {
            return;
        }
        $response = $this->request(['game_delete', ['id' => $game->getId()]], [], 'DELETE');
        $this->checkStrategyRequestResponse($response, 'Deleting different user\'s game action', Response::HTTP_FORBIDDEN, HttpException::CODE_ACCESS_DENIED, 'access denied');
    }


    private function checkIsCorrectGameParamsInResponse(ApiResponse $response, string $testKeysID, int $sum = null, string $name = null, string $description = null, int $rounds = null, int $balesForWin = null, int $balesForLoos = null, int $balesForCooperation = null, int $balesForDraw = null, User $user = null): Game
    {
        // Check response status - mus be equals to 200
        $this->assertEquals(Response::HTTP_OK, $response->getStatus(),
            sprintf('Wrong test "%s" response, status code must be equal to %s, but it is not. It is: %s. The content is: %s',
                $testKeysID, Response::HTTP_OK, $response->getStatus(), $response->getContent()));

        // Check is response data has all necessary params (and if it's has correct values)
        $data = $response->getData();
        $baseMessage = 'Wrong test "' . $testKeysID . '" response. ';

        // Check all game params (are they exists and have correct values types)
        $this->checkParamsOfGameFromResponse($data, $response->getContent(), $baseMessage);

        // Check game attributes values
        $info = $data['info'];
        $params = $data['params'];

        $mustBeEqualsMessage = 'Wrong test "' . $testKeysID . '" response format, the response param "info" must have a "%s" param equals to "%s", but it is not, it is "%s"';
        if ($name !== null) {
            $this->assertEquals($info['name'], $name, sprintf($mustBeEqualsMessage, 'name', $name, $info['name']));
        }
        if ($sum !== null) {
            $this->assertEquals($info['sum'], $sum, sprintf($mustBeEqualsMessage,  'sum', $sum, $info['sum']));
        }
        if ($description !== null) {
            $this->assertEquals($info['description'], $description, sprintf($mustBeEqualsMessage,  'description', $description, $info['description']));
        }

        $mustBeEqualsMessage = 'Wrong test "' . $testKeysID . '" response format, the response param "params" must have a "%s" param equals to "%s", but it is not, it is "%s"';
        if ($rounds !== null) {
            $this->assertEquals($params['rounds'], $rounds, sprintf($mustBeEqualsMessage, 'rounds', $rounds, $params['rounds']));
        }
        if ($balesForWin !== null) {
            $this->assertEquals($params['balesForWin'], $balesForWin, sprintf($mustBeEqualsMessage, 'balesForWin', $balesForWin, $params['balesForWin']));
        }
        if ($balesForLoos !== null) {
            $this->assertEquals($params['balesForLoos'], $balesForLoos, sprintf($mustBeEqualsMessage, 'balesForLoos', $balesForLoos, $params['balesForLoos']));
        }
        if ($balesForCooperation !== null) {
            $this->assertEquals($params['balesForCooperation'], $balesForCooperation, sprintf($mustBeEqualsMessage, 'balesForCooperation', $balesForCooperation, $params['balesForCooperation']));
        }
        if ($balesForDraw !== null) {
            $this->assertEquals($params['balesForDraw'], $balesForDraw, sprintf($mustBeEqualsMessage, 'balesForDraw', $balesForDraw, $params['balesForDraw']));
        }

        // Find this game
        $game = $this->entityManager->getRepository(Game::class)->findOneBy(['id' => $info['id']]);
        $this->assertNotEmpty($game, sprintf('Test "%s" failed. Can not fund game by ID %s', $testKeysID, $info['id']));
        $this->entityManager->refresh($game);

        // Check dependencies between user and this game
        if ($user !== null) {
            $this->assertNotNull($game, sprintf('Test "%s" is failed. Can`t find the game #%s in DB', $testKeysID, $info['id']));
            // Check is strategy has user
            $this->assertNotNull($game->getUser(), sprintf('Test "%s" is failed. Game #%s has no user', $testKeysID, $info['id']));
            // Check is strategy has correct user ID
            $this->assertEquals($game->getUser()->getId(), $user->getId(),
                sprintf('Test "%s" is failed. Game #%s has not correct user. Game user ID is %s, but must be %s',
                    $testKeysID, $info['id'], $game->getUser()->getId(), $user->getId()));
            // Check is user has this strategy
            $this->assertTrue($user->getGames()->contains($game), sprintf('Test "%s" failed. User #%s has not game #%s', $testKeysID, $user->getId(), $info['id']));
        }

        return $game;
    }

    private function checkIsCorrectGameResults(Game $game, int $strategiesCount, array $gameResults, string $testKeysID)
    {
        // Check is game has correct count of results
        $gameResultsCount = $game->getResults()->count();
        $this->assertEquals($strategiesCount, $gameResultsCount, sprintf('Testing "%s" is failed. Game #%s must have %s results, but it\'s not. It has %s results',
            $testKeysID, $game->getId(), $strategiesCount, $gameResultsCount));

        // Get game params strategies id's, total results, individual results and sum for both results
        $totalSum = 0;
        $individualSum = 0;
        $totalResults = [];
        $individualResults = [];
        
        foreach ($gameResults['total'] as $totalRes) {
            $totalResults[$totalRes['id']] = $totalRes;
            $totalSum += $totalRes['result'];
        }
        foreach ($gameResults['individual'] as $id => $individualRes) {
            if (!isset($individualResults[$id])) {
                $individualResults[$id] = [];
            }
            foreach ($individualRes as $res) {
                $individualSum += $res['result'];
                $individualResults[$id][$res['partnerID']] = $res;
            }

        }

        $baseMessage = 'Testing "' . $testKeysID . '" is failed. Game #' . $game->getId() . ' ';

        // Check game sum
        $incorrectSumMessage = $baseMessage . 'must have %s sum %s, but it\'s not, It has %s';
        $this->assertEquals($gameResults['sum'], $totalSum, sprintf($incorrectSumMessage, 'total', $gameResults['sum'], $totalSum));
        $this->assertEquals($gameResults['sum'], $individualSum, sprintf($incorrectSumMessage, 'individual', $gameResults['sum'], $individualSum));

        // Check is game has the same results and individual results like params
        $gameHasNotPresentedResultMessage = $baseMessage . 'has an %s result #%s, but it\'s not presented in game params';
        $gameHasIncorrectResultValue = $baseMessage . '%s result #%s must have a "%s" param equals to %s, but it\'s not. It has value %s';
        foreach ($game->getResults() as $gameResult) {
            $resultID = $gameResult->getStrategy()->getId();
            $resultIndividualResultsCount = $gameResult->getIndividualGameResults()->count();

            $this->assertEquals($strategiesCount - 1, $resultIndividualResultsCount,
                sprintf($baseMessage . 'result #%s must have %s individual results, but it\'s not. It has %s',
                    $resultID, $strategiesCount - 1, $resultIndividualResultsCount));

            $this->assertArrayHasKey($resultID, $totalResults, sprintf($gameHasNotPresentedResultMessage, $game->getId(), 'total', $resultID));
            $this->assertArrayHasKey($resultID, $individualResults, sprintf($gameHasNotPresentedResultMessage, $game->getId(), 'individual', $resultID));

            $totalRes = $totalResults[$resultID];
            $this->assertEquals($totalRes['id'], $resultID,
                sprintf($gameHasIncorrectResultValue, 'total', $resultID, 'id', $totalRes['id'], $resultID));
            $this->assertEquals($totalRes['name'], $gameResult->getStrategy()->getName(),
                sprintf($gameHasIncorrectResultValue, 'total', $resultID, 'name', $totalRes['name'], $gameResult->getStrategy()->getName()));
            $this->assertEquals($totalRes['result'], $gameResult->getResult(),
                sprintf($gameHasIncorrectResultValue, 'total', $resultID, 'result', $totalRes['result'], $gameResult->getResult()));

            foreach ($gameResult->getIndividualGameResults() as $gameIndividualGameRes) {
                $individualResultID = $gameIndividualGameRes->getPartner()->getId();
                $individualID = sprintf('%s[#%s]', $resultID, $individualResultID);

                $this->assertArrayHasKey($individualResultID, $individualResults[$resultID],
                    sprintf($baseMessage . 'result\'s #%s individual result #%s is not presented in game params individual results',
                        $resultID, $individualResultID));

                $individualResult = $individualResults[$resultID][$individualResultID];
                $this->assertEquals($individualResult['result'], $gameIndividualGameRes->getResult(),
                    sprintf($gameHasIncorrectResultValue, 'individual', $individualID, 'result', $individualResult['result'], $gameIndividualGameRes->getResult()));
                $this->assertEquals($individualResult['partnerResult'], $gameIndividualGameRes->getPartnerResult(),
                    sprintf($gameHasIncorrectResultValue, 'individual', $individualID, 'partnerResult', $individualResult['partnerResult'], $gameIndividualGameRes->getPartnerResult()));
                $this->assertEquals($individualResult['partnerID'], $individualResultID,
                    sprintf($gameHasIncorrectResultValue, 'individual', $individualID, 'partnerID', $individualResult['partnerID'], $individualResultID));
                $this->assertEquals($individualResult['partnerName'], $gameIndividualGameRes->getPartner()->getName(),
                    sprintf($gameHasIncorrectResultValue, 'individual', $individualID, 'partnerName', $individualResult['partnerName'], $gameIndividualGameRes->getPartner()->getName()));
            }
        }
    }

    private function checkStrategyRequestResponse(ApiResponse $response, string $testKeysID, int $expectedStatus = null, int $expectedCode = null, $expectedPhrase = null)
    {
        if ($expectedStatus === null) {
            $expectedStatus = Response::HTTP_OK;
        }

        $baseMessage = 'Wrong test "' . $testKeysID . '" response. ';

        $this->assertEquals($expectedStatus, $response->getStatus(),
            sprintf($baseMessage . 'Status code must be equal to %s, but it is not. It is: %s. The content is: %s',
                $expectedStatus, $response->getStatus(), $response->getContent()));

        $data = $response->getData();
        $mustContainsMessage = $baseMessage . 'Response must contains the "%s" param, but it is not. It is: %s';

        if ($expectedStatus === Response::HTTP_OK) {
            if (is_array($expectedPhrase)) {
                $this->checkParamsOfGameFromResponse($data, $response->getContent(), $baseMessage);
            } elseif ($expectedPhrase === '"OK"') {
                $this->assertEquals($expectedPhrase, $response->getContent(),
                    sprintf($baseMessage . 'The response body must be equals to "%s", but it\'s not. It\'s "%s"', $expectedPhrase, $response->getContent()));
            } elseif ($expectedPhrase !== null) {
                $this->assertContains($expectedPhrase, strtolower($response->getContent()),
                    sprintf($baseMessage . 'The response body must contains "%s", but it\'s not. It\'s "%s"', $expectedPhrase, $response->getContent()));
            }
        } else {
            $this->assertArrayHasKey('error', $data, sprintf($mustContainsMessage, 'error', $response->getContent()));

            $error = $data['error'];
            $errorJson = json_encode($error);

            $mustContainsMessage = $baseMessage . 'Response param "error" must contains the "%s" param, but it is not. It is: %s';
            $this->assertArrayHasKey('message', $error, sprintf($mustContainsMessage, 'rounds', $errorJson));
            $this->assertArrayHasKey('code', $error, sprintf($mustContainsMessage, 'balesForWin', $errorJson));

            $typeMustBeMessage = $baseMessage . 'The response "%s" param must have a type "%s". The response content param value is %s';
            $this->assertInternalType('string', $error['message'], sprintf($typeMustBeMessage, 'error[message]', 'string', $error['message']));
            $this->assertInternalType('integer', $error['code'], sprintf($typeMustBeMessage, 'error[code]', 'integer', $error['code']));

            if ($expectedCode !== null) {
                $this->assertEquals($error['code'], $expectedCode,
                    sprintf( $baseMessage . 'The response param "error" must have a "code" param equals to "%s", but it is not, it is "%s"',
                        $expectedCode, $error['code']));
            }

            if ($expectedPhrase !== null) {
                $this->assertContains($expectedPhrase, strtolower($error['message']),
                    sprintf($baseMessage . 'The response param "error" must have a "message" param that contains a "%s" substring, but it is not, it is "%s"',
                        $expectedPhrase, $error['message']));
            }
        }
    }

    private function checkParamsOfGameFromResponse(array $data, string $content, string $baseMessage)
    {
        $mustContainsMessage = $baseMessage . 'Response must contains the "%s" param, but it is not. It is: %s';

        // Check is response data has all necessary params (and if it's has correct values)
        $this->assertArrayHasKey('info', $data, sprintf($mustContainsMessage, 'info', $content));
        $this->assertArrayHasKey('params', $data, sprintf($mustContainsMessage, 'params', $content));

        // Check data structure of game data "info" and "params" components
        $this->checkGameInfoFromResponse($data['info'], $baseMessage);
        $this->checkGameParamsFromResponse($data['params'], $baseMessage);
    }

    private function checkGameInfoFromResponse(array $info, string $baseMessage, array $additionalParams = [])
    {
        $mustContainsMessage = $baseMessage . 'Response param "info" must contains the "%s" param, but it is not. It is: %s';
        $typeMustBeMessage = $baseMessage . 'The response "%s" param must have a type "%s". The response content param value is %s';
        $infoJson = json_encode($info);

        $params = array_merge([
            ['id', 'integer'],
            ['name', 'string'],
            ['sum', 'integer'],
            ['description', 'string'],
            ['date', 'string'],
        ], $additionalParams);

        foreach ($params as $param) {
            $type = null;
            if (is_array($param)) {
                $type = $param[1];
                $param = $param[0];
            }
            $this->assertArrayHasKey($param, $info, sprintf($mustContainsMessage, $param, $infoJson));
            if ($type !== null) {
                $this->assertInternalType($type, $info[$param], sprintf($typeMustBeMessage, "info[{$param}]", $type, $info[$param]));
            }
        }
    }

    private function checkGameParamsFromResponse(array $data, string $baseMessage, array $additionalParams = [])
    {
        $mustContainsMessage = $baseMessage . 'The response param "params" must contains the "%s" param, but it is not. It is: %s';
        $typeMustBeMessage = $baseMessage . 'The "%s" param must have a type "%s". The response content param value is %s';
        $dataJson = json_encode($data);

        $params = array_merge([
            ['rounds', 'integer'],
            ['balesForWin', 'integer'],
            ['balesForLoos', 'integer'],
            ['balesForCooperation', 'integer'],
            ['balesForDraw', 'integer'],
        ], $additionalParams);

        foreach ($params as $param) {
            $type = null;
            if (is_array($param)) {
                $type = $param[1];
                $param = $param[0];
            }
            $this->assertArrayHasKey($param, $data, sprintf($mustContainsMessage, $param, $dataJson));
            if ($type !== null) {
                $this->assertInternalType($type, $data[$param], sprintf($typeMustBeMessage, "params[{$param}]", $type, $data[$param]));
            }
        }
    }


    private function calculateGameResultsCount(Game $game)
    {
        return $this->entityManager->getRepository(GameResult::class)
            ->createQueryBuilder('gr')
            ->select('COUNT(gr)')
            ->andWhere('gr.game = :game')
            ->setParameter('game', $game)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function calculateGameSesultsSum(Game $game)
    {
        return $this->entityManager->getRepository(GameResult::class)
            ->createQueryBuilder('gr')
            ->select('SUM(gr.result)')
            ->andWhere('gr.game = :game')
            ->setParameter('game', $game)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function calculateGameIndividualResultsCount(Game $game)
    {
        $resultsIDsQuery = $this->entityManager->getRepository(GameResult::class)
            ->createQueryBuilder('gr')
            ->select('gr.id')
            ->andWhere('gr.game = :game')
            ->setParameter('game', $game);
        $resultsIDs = array_map(function ($res) { return intval($res['id']); }, $resultsIDsQuery->getQuery()->getScalarResult());

        return $this->entityManager->getRepository(IndividualGameResult::class)
            ->createQueryBuilder('igr')
            ->select('COUNT(igr)')
            ->andWhere('igr.gameResult IN (:game_results_ids)')
            ->setParameter('game_results_ids', $resultsIDs)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function calculateGameIndividualResultsSum(Game $game)
    {
        $resultsIDsQuery = $this->entityManager->getRepository(GameResult::class)
            ->createQueryBuilder('gr')
            ->select('gr.id')
            ->andWhere('gr.game = :game')
            ->setParameter('game', $game);
        $resultsIDs = array_map(function ($res) { return intval($res['id']); }, $resultsIDsQuery->getQuery()->getScalarResult());

        return $this->entityManager->getRepository(IndividualGameResult::class)
            ->createQueryBuilder('igr')
            ->select('SUM(igr.result)')
            ->andWhere('igr.gameResult IN (:game_results_ids)')
            ->setParameter('game_results_ids', $resultsIDs)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function createGameParams(array $strategiesIDs = [], array $gameResults = [], string $name = null, string $description = null, int $rounds = null, int $balesForWin = null, int $balesForLoos = null, int $balesForCooperation = null, int $balesForDraw = null): array
    {
        $faker = Factory::create();

        if (empty($strategiesIDs)) {
            $strategiesIDs = $this->getUserStrategiesIds();
        }

        if (empty($gameResults)) {
            $gameResults = $this->getGameService()->runGame($this->user, $strategiesIDs);
        }

        $gameConfig = [
            'rounds' => $rounds !== null ? $rounds : $faker->numberBetween(10, 30),
            'balesForWin' => $balesForWin !== null ? $balesForWin : $faker->numberBetween(10, 30),
            'balesForLoos' => $balesForLoos !== null ? $balesForLoos : $faker->numberBetween(-20, 0),
            'balesForCooperation' => $balesForCooperation !== null ? $balesForCooperation : $faker->numberBetween(0, 15),
            'balesForDraw' => $balesForDraw !== null ? $balesForDraw : $faker->numberBetween(5, 25),
        ];

        return [
            'game_form' => array_merge([
                'name' => $name !== null ? $name : 'Game ' . $faker->name,
                'description' => $description !== null ? $description : $faker->text,
                'resultsData' => $gameResults
            ], $gameConfig)
        ];
    }

    /**
     * @param int $count
     * @param User|null $user
     * @return Strategy[]|Strategy
     */
    private function findUserStrategies(int $count = 1, User $user = null)
    {
        if ($user === null) {
            $user = $this->user;
        }

        /** @var \App\Repository\StrategyRepository $strategyRepository */
        $strategyRepository = $this->entityManager->getRepository(Strategy::class);
        /** @var Strategy[] $strategies */
        $strategies = $strategyRepository->createQueryBuilder('s')
            ->andWhere('s.user = :user')
            ->andWhere('s.status = :status_enabled')
            ->setMaxResults($count)
            ->setParameter('status_enabled', IsEnabledEnum::TYPE_ENABLED)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        return $count > 1 ? $strategies : array_shift($strategies);
    }

    private function findGame(int $userID = null, int $gameID = null): ?Game
    {
        if ($userID === null && $this->user !== null) {
            $userID = $this->user->getId();
        }
        if ($userID === null) {
            return null;
        }

        if ($gameID === null) {
            $criteria = ['user' => $userID];
        } else {
            $criteria = ['id' => $gameID];
        }
        return $this->entityManager->getRepository(Game::class)->findOneBy($criteria);
    }

    private function findNotUserGame(int $userID = null): ?Game
    {
        if ($userID === null && $this->user !== null) {
            $userID = $this->user->getId();
        }
        if ($userID === null) {
            return null;
        }
        return $this->entityManager->getRepository(Game::class)->createQueryBuilder('g')
            ->andWhere('g.user != :user')
            ->setParameter('user', $userID)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function getUserStrategiesIds(int $count = 0): array
    {
        if ($count === 0) {
            $faker = Factory::create();
            $count = $faker->numberBetween(3, 7);
        }
        $strategies = $this->findUserStrategies($count);
        $strategiesIDs = [];
        foreach ($strategies as $strategy) {
            $strategiesIDs[] = $strategy->getId();
        }
        return $strategiesIDs;
    }

    private function getGameService(): GameService
    {
        if ($this->gameService !== null) {
            return $this->gameService;
        }
        return $this->gameService = new GameService($this->entityManager, $this->getStrategyDecisionsService(), $this->getGameResultsService(), self::$kernel->getContainer());
    }

    private function getStrategyDecisionsService(): StrategyDecisionsService
    {
        if ($this->strategyDecisionsService !== null) {
            return $this->strategyDecisionsService;
        }
        return $this->strategyDecisionsService = new StrategyDecisionsService($this->entityManager, self::$kernel->getContainer());
    }

    private function getGameResultsService(): GameResultsService
    {
        if ($this->gameResultsService !== null) {
            return $this->gameResultsService;
        }
        return $this->gameResultsService = new GameResultsService($this->entityManager, self::$kernel->getContainer());
    }
}