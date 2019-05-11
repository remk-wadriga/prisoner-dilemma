<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 25.10.2018
 * Time: 14:43
 */

namespace App\Controller;

use App\Entity\Game;
use App\Service\Statistics\TotalStatisticsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\GameService;
use App\Service\StrategyService;

class ParamsController extends ControllerAbstract
{
    public function getRequestFilters(Request $request): array
    {
        if ($request->getPathInfo() == '/params/game-filters') {
            return [
                'fromDate',
                'toDate',
                'game_roundsCount' => 'integer',
                'game_balesForWin' => 'integer',
                'game_balesForLoos' => 'integer',
                'game_balesForCooperation' => 'integer',
                'game_balesForDraw' => 'integer',
            ];
        }
        return [];
    }

    /**
     * @Route("/params/game", name="params_game", methods={"GET"})
     */
    public function game(GameService $gameService)
    {
        return $this->json($gameService->getParams());
    }

    /**
     * @Route("/params/strategy", name="params_strategy", methods={"GET"})
     */
    public function strategy(StrategyService $strategyService)
    {
        return $this->json($strategyService->getParams());
    }

    /**
     * @Route("/params/statistics-dates", name="params_statistics_dates", methods={"GET"})
     */
    public function statisticsDates(TotalStatisticsService $statisticsService)
    {
        return $this->json($statisticsService->getFirstAndLastGamesDates($this->getUser()));
    }

    /**
     * @Route("/params/game-filters", name="params_game_filters", methods={"GET"})
     */
    public function gameFilters(GameService $gameService)
    {
        return $this->json($gameService->gamesFilters($this->getUser()));
    }
}