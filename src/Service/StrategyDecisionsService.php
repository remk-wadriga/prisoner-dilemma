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
use App\Exception\StrategyServiceException;
use App\Repository\DecisionRepository;
use Doctrine\Common\Collections\Collection;
use App\Entity\Types\Enum\DecisionTypeEnum;
use Doctrine\ORM\NonUniqueResultException;

class StrategyDecisionsService extends AbstractService
{
    const PORT_TYPE_IN = 'in';
    const PORT_TYPE_OUT = 'out';

    static $allowedPortTypes = [self::PORT_TYPE_IN, self::PORT_TYPE_OUT];

    private $randomDecisionChance = 10;
    private $copyDecisionChance = 20;
    private $acceptDecisionChance = 50;

    public function setRandomDecisionChance(int $chance)
    {
        $this->randomDecisionChance = $chance;
    }

    public function setCopyDecisionChance(int $chance)
    {
        $this->copyDecisionChance = $chance;
    }

    public function setAcceptDecisionChance(int $chance)
    {
        $this->acceptDecisionChance = $chance;
    }

    public function getParams()
    {
        return [
            'randomDecisionChance' => $this->randomDecisionChance,
            'copyDecisionChance' => $this->copyDecisionChance,
            'acceptDecisionChance' => $this->acceptDecisionChance,
        ];
    }

    /**
     * @param Strategy $strategy
     * @return array|null
     * @throws StrategyServiceException
     */
    public function createDecisionsDataArray(Strategy $strategy): ?array
    {
        /** @var DecisionRepository $repository */
        $repository = $this->entityManager->getRepository(Decision::class);
        try {
            $rootDecision = $repository->findRootByStrategyId($strategy->getId());
        } catch (NonUniqueResultException $e) {
            throw new StrategyServiceException(sprintf('Strategy %s root decision is not unique', $strategy->getId()), StrategyServiceException::CODE_INVALID_STRATEGY_DATA);
        }

        if ($rootDecision === null) {
            return null;
        }

        return [
            'type' => $rootDecision->getType(),
            'children' => $this->getDecisionChildrenDataRecursively($rootDecision->getChildren()),
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
        if ($type === null && $this->faker->boolean($this->copyDecisionChance)) {
            $type = DecisionTypeEnum::TYPE_COPY;
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
     * @throws StrategyServiceException
     */
    public function generateDecisionTreeByParamsRecursively(Strategy $strategy, array $params = []): Decision
    {
        // Get decisions data array
        if (empty($params)) {
            $params = $strategy->getDecisionsData();
        }

        // Check decisions data
        if (!isset($params['type'])) {
            throw new StrategyServiceException('Param "type" is missed', StrategyServiceException::CODE_INVALID_PARAMS);
        }
        if (!in_array($params['type'], DecisionTypeEnum::getAvailableTypes())) {
            throw new StrategyServiceException(sprintf('Invalid value for param "type": "%s"', $params['type']), StrategyServiceException::CODE_INVALID_PARAMS);
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
            throw new StrategyServiceException('Param "children mus be an array"', StrategyServiceException::CODE_INVALID_PARAMS);
        }

        // Create objects for all children recursively
        foreach ($params['children'] as $childParams) {
            if (empty($childParams)) {
                continue;
            }
            if (!is_array($childParams)) {
                throw new StrategyServiceException('Child is not an array', StrategyServiceException::CODE_INVALID_PARAMS);
            }
            $decision->addChild($this->generateDecisionTreeByParamsRecursively($strategy, $childParams));
        }

        return $decision;
    }


    /**
     * @param Collection $children
     * @return array
     */
    private function getDecisionChildrenDataRecursively(Collection $children)
    {
        $result = [];

        // Stop condition - when is no children left
        if ($children->count() === 0) {
            return $result;
        }

        foreach ($children as $decision) {
            $result[] = [
                'type' => $decision->getType(),
                'children' => $this->getDecisionChildrenDataRecursively($decision->getChildren()),
            ];
        }

        return $result;
    }
}