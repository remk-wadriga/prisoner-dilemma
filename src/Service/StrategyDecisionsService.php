<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 20.09.2018
 * Time: 10:39
 */

namespace App\Service;

use App\Exception\StrategyException;
use App\Entity\Strategy;
use App\Entity\Decision;
use App\Repository\DecisionRepository;
use Doctrine\Common\Collections\Collection;
use App\Entity\Types\Enum\DecisionTypeEnum;

class StrategyDecisionsService extends AbstractService
{
    const PORT_TYPE_IN = 'in';
    const PORT_TYPE_OUT = 'out';

    static $allowedPortTypes = [self::PORT_TYPE_IN, self::PORT_TYPE_OUT];

    private $randomDecisionChance = 15;
    private $acceptDecisionChance = 50;

    public function setRandomDecisionChance(int $chance)
    {
        $this->randomDecisionChance = $chance;
    }

    public function setAcceptDecisionChance(int $chance)
    {
        $this->acceptDecisionChance = $chance;
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
        if ($rootDecision === null) {
            return null;
        }

        return [
            'type' => $rootDecision->getType(),
            'children' => $this->getDecisionChildrenRecursively($rootDecision->getChildren()),
        ];
    }

    /**
     * @param Strategy $strategy
     * @param Decision|null $parent
     * @param string|null $type
     * @return Decision
     */
    public function generateRandomDecision(Strategy $strategy, Decision $parent = null, $type = null): Decision
    {
        if ($type === null) {
            $type = $this->getRandomDecisionType();
        }

        return (new Decision())
            ->setStrategy($strategy)
            ->setParent($parent)
            ->setType($type);
    }

    public function getRandomDecisionType(): string
    {
        $type = null;
        if ($this->faker->boolean($this->randomDecisionChance)) {
            $type = DecisionTypeEnum::TYPE_RANDOM;
        }
        if ($type === null && $this->faker->boolean($this->acceptDecisionChance)) {
            $type = DecisionTypeEnum::TYPE_ACCEPT;
        }
        if ($type === null) {
            $type = DecisionTypeEnum::TYPE_REFUSE;
        }
        return $type;
    }

    /**
     * @param Strategy $strategy
     * @param array $params
     * @return Decision
     * @throws StrategyException
     */
    public function generateDecisionTreeByParamsRecursively(Strategy $strategy, array $params = []): Decision
    {
        // Get decisions data array
        if (empty($params)) {
            $params = $strategy->getDecisionsData();
        }

        // Check decisions data
        if (!isset($params['type'])) {
            throw new StrategyException('Param "type" is missed', StrategyException::CODE_INVALID_PARAMS);
        }
        if (!in_array($params['type'], DecisionTypeEnum::getAvailableTypes())) {
            throw new StrategyException(sprintf('Invalid value for param "type": "%s"', $params['type']), StrategyException::CODE_INVALID_PARAMS);
        }

        // Create new decision
        $decision = (new Decision())
            ->setStrategy($strategy)
            ->setType($params['type']);

        // Stop condition - when decision has no children
        if (empty($params['children'])) {
            return $decision;
        }

        // Check children param
        if (!is_array($params['children'])) {
            throw new StrategyException('Param "children mus be an array"', StrategyException::CODE_INVALID_PARAMS);
        }

        // Create objects for all children recursively
        foreach ($params['children'] as $childParams) {
            if (empty($childParams)) {
                continue;
            }
            if (!is_array($childParams)) {
                throw new StrategyException('Child is not an array', StrategyException::CODE_INVALID_PARAMS);
            }
            $decision->addChild($this->generateDecisionTreeByParamsRecursively($strategy, $childParams));
        }

        return $decision;
    }

    /**
     * @param Collection $children
     * @return array
     */
    private function getDecisionChildrenRecursively(Collection $children)
    {
        $result = [];

        // Stop condition - when is no children left
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