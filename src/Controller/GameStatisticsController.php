<?php


namespace App\Controller;


use App\Service\Statistics\GameStatisticsService;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Game;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class GameStatisticsController extends ControllerAbstract
{
    private $statisticsService;

    public function __construct(GameStatisticsService $gameStatisticsService)
    {
        $this->statisticsService = $gameStatisticsService;
    }

    /**
     * @Route("/game/{id}/statistics-by-strategies", name="game_statistics_by_strategies", methods={"GET"})
     * @IsGranted("MANAGE", subject="game")
     */
    public function byStrategies(Game $game)
    {
        return $this->json($this->statisticsService->getStatisticsByStrategies($game));
    }
}