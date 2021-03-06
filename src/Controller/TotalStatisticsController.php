<?php

namespace App\Controller;

use PDO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Statistics\TotalStatisticsService;

class TotalStatisticsController extends ControllerAbstract
{
    private $statisticsService;

    public function getRequestFilters(Request $request): array
    {
        return [
            'fromDate',
            'toDate',
            'game_roundsCount' => PDO::PARAM_INT,
            'game_balesForWin' => PDO::PARAM_INT,
            'game_balesForLoos' => PDO::PARAM_INT,
            'game_balesForCooperation' => PDO::PARAM_INT,
            'game_balesForDraw' => PDO::PARAM_INT,
        ];
    }

    public function __construct(TotalStatisticsService $totalStatisticsService)
    {
        $this->statisticsService = $totalStatisticsService;
    }

    /**
     * @Route("/total-statistics-by-dates", name="total_statistics_by_dates", methods={"GET"})
     */
    public function byDates()
    {
        return $this->json($this->statisticsService->getStatisticsByDates($this->getUser()));
    }

    /**
     * @Route("/total-statistics-by-strategies", name="total_statistics_by_strategies", methods={"GET"})
     */
    public function byStrategies()
    {
        return $this->json($this->statisticsService->getStatisticsByStrategies($this->getUser()));
    }

    /**
     * @Route("/total-statistics-by-games", name="total_statistics_by_games", methods={"GET"})
     */
    public function byGames()
    {
        return $this->json($this->statisticsService->getStatisticsByGames($this->getUser()));
    }

    /**
     * @Route("/total-statistics-by-rounds-count", name="total_statistics_by_rounds_count", methods={"GET"})
     */
    public function byRoundsCount()
    {
        return $this->json($this->statisticsService->getStatisticsByRoundsCount($this->getUser()));
    }
}