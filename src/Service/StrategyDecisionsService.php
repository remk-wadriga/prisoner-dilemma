<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 20.09.2018
 * Time: 10:39
 */

namespace App\Service;

use App\Exception\StrategyException;
use Faker\Factory;
use App\Entity\Strategy;
use App\Entity\Decision;
use App\Repository\DecisionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;
use App\Entity\Types\Enum\DecisionTypeEnum;

class StrategyDecisionsService extends AbstractService
{
    const PORT_TYPE_IN = 'in';
    const PORT_TYPE_OUT = 'out';

    static $allowedPortTypes = [self::PORT_TYPE_IN, self::PORT_TYPE_OUT];

    /** @var \Faker\Generator */
    private $faker;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->faker = Factory::create();
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
            $randomInteger = $this->faker->numberBetween(1, 3);
            switch ($randomInteger) {
                case 1:
                    $type = DecisionTypeEnum::TYPE_ACCEPT;
                    break;
                case 2:
                    $type = DecisionTypeEnum::TYPE_REFUSE;
                    break;
                default:
                    $type = DecisionTypeEnum::TYPE_RANDOM;
            }
        }

        return (new Decision())
            ->setStrategy($strategy)
            ->setParent($parent)
            ->setType($type);
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

        // Return condition - when decision has no children
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