<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 23.10.2018
 * Time: 18:22
 */

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\GameService;

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
        $results = $gameService->runGame($user, $strategiesIds, true, $request->request->get('rounds'));

        return $this->json([
            'rounds' => $gameService->getRoundsCount(),
            'results' => $results,
        ]);
    }
}