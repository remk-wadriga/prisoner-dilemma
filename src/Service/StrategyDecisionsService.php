<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 20.09.2018
 * Time: 10:39
 */

namespace App\Service;

use App\Entity\Strategy;
use App\Entity\Decision;
use App\Repository\DecisionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;

class StrategyDecisionsService extends AbstractService
{
    const PORT_TYPE_IN = 'in';
    const PORT_TYPE_OUT = 'out';

    static $allowedPortTypes = [self::PORT_TYPE_IN, self::PORT_TYPE_OUT];

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Strategy $strategy
     * @return array|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function parseDecisionsData(Strategy $strategy)
    {
        /** @var DecisionRepository $repository */
        $repository = $this->entityManager->getRepository(Decision::class);
        $rootDecision = $repository->findRootByStrategyId($strategy->getId());

        return [
            'type' => $rootDecision->getType(),
            'children' => $this->getDecisionChildrenRecursively($rootDecision->getChildren()),
        ];
    }

    /**
     * @param Collection $children
     * @return array
     */
    private function getDecisionChildrenRecursively(Collection $children)
    {
        $result = [];
        if ($children->count() === 0) {
            return $result;
        }

        foreach ($children as $decision) {
            $result[] = [
                'type' => $decision->getType(),
                'children' => $this->getDecisionChildrenRecursively($decision->getChildren()),
            ];
        }

        return $result;
    }
}