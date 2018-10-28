<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 23.10.2018
 * Time: 18:22
 */

namespace App\Controller;

use App\Entity\Game;
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

        // Save user entity
        $em = $this->getDoctrine()->getManager();
        $em->persist($game);
        $em->flush();

        return $this->json($this->gameInfo($game));
    }


    private function gameInfo(Game $game)
    {
        return [
            'id' => $game->getId(),
            'name' => $game->getName(),
            'description' => $game->getDescription(),
        ];
    }
}