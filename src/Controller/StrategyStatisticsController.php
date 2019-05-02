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
    /**
     * @Route("/strategy-statistics/{id}", name="statistics_strategy", methods={"GET"})
     * @IsGranted("MANAGE", subject="strategy")
     */
    public function strategyStatistics(Strategy $strategy, StrategyStatisticsService $strategyStatisticsService)
    {
        return $this->json([
            'strategy' => $this->strategyInfo($strategy),
            'statistics' => $strategyStatisticsService->getStatisticsByRoundsCount($strategy),
        ]);
    }


    protected function strategyInfo(Strategy $strategy, array $additionalFields = []): array
    {
        $params = [
            'id' => $strategy->getId(),
            'name' => $strategy->getName(),
            'description' => $strategy->getDescription(),
            'status' => $strategy->getStatus(),
        ];

        foreach ($additionalFields as $index => $field) {
            if (is_array($field)) {
                $params[$index] = $field;
            } else {
                $getter = 'get' . ucfirst($field);
                if (method_exists($strategy, $getter)) {
                    $params[$field] = $strategy->$getter();
                }
            }
        }

        return $params;
    }
}