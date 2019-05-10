<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 11.12.2018
 * Time: 01:16
 */

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use App\Service\Statistics\StrategyStatisticsService;
use App\Entity\Strategy;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class StrategyStatisticsController extends ControllerAbstract
{
    private $statisticsService;

    public function getRequestFilters(): array
    {
        return [
            'fromDate',
            'toDate',
        ];
    }

    public function __construct(StrategyStatisticsService $strategyStatisticsService)
    {
        $this->statisticsService = $strategyStatisticsService;
    }

    /**
     * @Route("/strategy/{id}/statistics-by-dates", name="strategy_statistics_by_dates", methods={"GET"})
     * @IsGranted("MANAGE", subject="strategy")
     */
    public function byDates(Strategy $strategy)
    {
        return $this->json($this->statisticsService->getStatisticsByDates($strategy));
    }

    /**
     * @Route("/strategy/{id}/statistics-by-rounds-count", name="strategy_statistics_by_rounds_count", methods={"GET"})
     * @IsGranted("MANAGE", subject="strategy")
     */
    public function byRoundsCount(Strategy $strategy)
    {
        return $this->json($this->statisticsService->getStatisticsByRoundsCount($strategy));
    }

    /**
     * @Route("/strategy/{id}/statistics-dates", name="strategy_statistics_dates", methods={"GET"})
     * @IsGranted("MANAGE", subject="strategy")
     */
    public function dates(Strategy $strategy)
    {
        return $this->json($this->statisticsService->getFirstAndLastGamesDates($strategy));
    }
}