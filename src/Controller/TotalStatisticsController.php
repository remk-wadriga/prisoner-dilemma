<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Statistics\TotalStatisticsService;

class TotalStatisticsController extends ControllerAbstract
{
    private $statisticsService;

    public function __construct(TotalStatisticsService $totalStatisticsService)
    {
        $this->statisticsService = $totalStatisticsService;
    }

    /**
     * @Route("/total-statistics-by-dates", name="total_statistics_by_dates", methods={"GET"})
     */
    public function byDates(Request $request)
    {
        return $this->json($this->getStatisticsService($request)->getStatisticsByDates($this->getUser()));
    }

    /**
     * @Route("/total-statistics-by-strategies", name="total_statistics_by_strategies", methods={"GET"})
     */
    public function byStrategies(Request $request)
    {
        return $this->json($this->getStatisticsService($request)->getStatisticsByStrategies($this->getUser()));
    }

    /**
     * @Route("/total-statistics-by-games", name="total_statistics_by_games", methods={"GET"})
     */
    public function byGames(Request $request)
    {
        return $this->json($this->getStatisticsService($request)->getStatisticsByGames($this->getUser()));
    }

    /**
     * @Route("/total-statistics-by-rounds-count", name="total_statistics_by_rounds_count", methods={"GET"})
     */
    public function byRoundsCount(Request $request)
    {
        return $this->json($this->getStatisticsService($request)->getStatisticsByRoundsCount($this->getUser()));
    }


    private function getStatisticsService(Request $request)
    {
        $this->statisticsService->filters = [
            'fromDate' => $request->get('fromDate'),
            'toDate' => $request->get('toDate'),
        ];
        return $this->statisticsService;
    }
}