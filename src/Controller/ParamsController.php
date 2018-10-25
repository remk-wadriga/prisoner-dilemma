<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 25.10.2018
 * Time: 14:43
 */

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use App\Service\GameService;
use App\Service\StrategyService;

class ParamsController extends ControllerAbstract
{
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
}