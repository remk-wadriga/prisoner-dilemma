<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 23.10.2018
 * Time: 18:22
 */

namespace App\Controller;

use App\Entity\Game;
use App\Service\GameResultsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\GameService;
use App\Form\GameForm;
use App\Exception\HttpException;
use App\Exception\GameServiceException;

class GameController extends ControllerAbstract
{
    /**
     * @Route("/game/start", name="game_start", methods={"GET", "POST"})
     */
    public function start(Request $request, GameService $gameService)
    {
        // Get current user
        $user = $this->getUser();

        // Get strategies IDs from request body
        $strategiesIds = $request->request->get('strategiesIds', []);
        if (!empty($stretegiesIDs)) {
            $strategiesIds = json_decode($stretegiesIDs, true);
        }

        // Play game with all selected (or just all enabled user strategies) and get results
        try {
            $results = $gameService->runGame($user, $strategiesIds,
                $request->request->get('rounds'),
                $request->request->get('balesForWin'),
                $request->request->get('balesForLoos'),
                $request->request->get('balesForCooperation'),
                $request->request->get('balesForDraw'),
                (bool)$request->request->get('writeIndividualResults', true));
        } catch (GameServiceException $e) {
            throw new HttpException('Game is failed', 0, $e);
        }

        return $this->json([
            'params' => $gameService->getParams(),
            'results' => $results,
        ]);
    }

    /**
     * @Route("/game/{id}", name="game_show", methods={"GET"})
     */
    public function show(Game $game, GameService $gameService, GameResultsService $gameResultsService)
    {
        // Build strategy decisions tree
        try {
            $results = $gameResultsService->createGameResultsDataArray($game);
        } catch (GameServiceException $e) {
            throw new HttpException('Can\'t build game results data', 0, $e);
        }

        return $this->json([
            'info' => $this->gameInfo($game),
            'params' => $gameService->getParams($game),
            'results' => $results,
        ]);
    }

    /**
     * @Route("/game", name="game_create", methods={"POST"})
     */
    public function create(Request $request, GameService $gameService)
    {
        // Create new game
        $game = new Game();

        // Process request
        $form = $this->createJsonForm(GameForm::class, $game);
        $this->handleJsonForm($form, $request);

        // Create game related entities for "total" and "individual" results
        try {
            $gameService->parseResultsData($game);
        } catch (GameServiceException $e) {
            throw new HttpException('Can\'t create game', 0, $e);
        }

        // Save game entity
        $em = $this->getDoctrine()->getManager();
        $em->persist($game);
        $em->flush();

        return $this->json([
            'info' => $this->gameInfo($game),
            'params' => $gameService->getParams($game),
        ]);
    }

    /**
     * @Route("/game/{id}", name="game_update", methods={"PUT"})
     */
    public function update(Game $game, Request $request, GameService $gameService)
    {
        // Process request
        $form = $this->createJsonForm(GameForm::class, $game);
        $this->handleJsonForm($form, $request);

        // Create game related entities for "total" and "individual" results
        try {
            $gameService->parseResultsData($game);
        } catch (GameServiceException $e) {
            throw new HttpException('Can\'t create game', 0, $e);
        }

        // Update game entity
        $em = $this->getDoctrine()->getManager();
        $em->persist($game);
        $em->flush();

        return $this->json([
            'info' => $this->gameInfo($game),
            'params' => $gameService->getParams($game),
        ]);
    }

    private function gameInfo(Game $game, array $additionalFields = [])
    {
        $params = [
            'id' => $game->getId(),
            'name' => $game->getName(),
            'description' => $game->getDescription(),
        ];

        foreach ($additionalFields as $index => $field) {
            if (is_array($field)) {
                $params[$index] = $field;
            } else {
                $getter = 'get' . ucfirst($field);
                if (method_exists($game, $getter)) {
                    $params[$field] = $game->$getter();
                }
            }
        }

        return $params;
    }
}