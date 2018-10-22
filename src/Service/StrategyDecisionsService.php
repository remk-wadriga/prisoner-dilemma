<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 20.09.2018
 * Time: 10:39
 */

namespace App\Service;

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