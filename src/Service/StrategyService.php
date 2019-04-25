<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 22.10.2018
 * Time: 13:11
 */

namespace App\Service;

use App\Repository\DecisionRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Strategy;
use App\Entity\Decision;
use Symfony\Component\DependencyInjection\ContainerInterface;

class StrategyService extends AbstractService
{
    private $decisionsService;
    private $maxRandomDecisionsCount = 10;
    private $chanceOfExtendingBranch = 80;

    public function __construct(EntityManagerInterface $entityManager, StrategyDecisionsService $decisionsService, ContainerInterface $container)
    {
        parent::__construct($entityManager, $container);
        $this->decisionsService = $decisionsService;
    }

    public function getParams()
    {
        return array_merge([
            'maxRandomDecisionsCount' => $this->maxRandomDecisionsCount,
            'chanceOfExtendingBranch' => $this->chanceOfExtendingBranch,
        ], $this->decisionsService->getParams());
    }

    /**
     * @param User $user
     * @param int $steps
     * @param string|null $name
     * @param int|null $chanceOfExtendingBranch
     * @param int|null $randomDecisionChance
     * @param int|null $copyDecisionChance
     * @param int|null $acceptDecisionChance
     * @return Strategy
     */
    public function generateRandomStrategy(User $user, $steps = 0, $name = null, $chanceOfExtendingBranch = null, $randomDecisionChance = null, $copyDecisionChance = null, $acceptDecisionChance = null): Strategy
    {
        if (!$steps) {
            $steps = $this->faker->numberBetween(1, $this->maxRandomDecisionsCount);
        }
        if (!$name) {
            $name = $this->faker->name . ' ' . $steps . ' steps';
        }
        if ((int)$chanceOfExtendingBranch > 0) {
            $this->chanceOfExtendingBranch = (int)$chanceOfExtendingBranch;
        }
        if ($randomDecisionChance !== null) {
            $this->decisionsService->setRandomDecisionChance((int)$randomDecisionChance);
        }
        if ($copyDecisionChance !== null) {
            $this->decisionsService->setCopyDecisionChance($copyDecisionChance);
        }
        if ($acceptDecisionChance !== null) {
            $this->decisionsService->setAcceptDecisionChance((int)$acceptDecisionChance);
        }

        // Create strategy
        $strategy = (new Strategy())
            ->setUser($user)
            ->setName($name)
            ->setDescription($this->faker->text)
        ;

        // Create decisions tree
        $rootDecision = $this->decisionsService->generateRandomDecision($strategy);
        $this->addDecisionsChildrenRecursively($rootDecision, $steps);

        // Add decisions tree to strategy
        $strategy->addDecision($rootDecision);

        return $strategy;
    }

    /**
     * @param Strategy $strategy
     * @throws \App\Exception\StrategyServiceException
     */
    public function parseDecisionsData(Strategy $strategy)
    {
        // If strategy decisions are not changed - we have nothing to do
        if ($strategy->getDecisionsData() === null) {
            return;
        }

        // Remove old decisions
        /** @var DecisionRepository $repository */
        $repository = $this->entityManager->getRepository(Decision::class);
        $decisions = $repository->findDecisionsByStrategyIdOrderedByIdDesc($strategy->getId());
        foreach ($decisions as $decision) {
            $strategy->removeDecision($decision);
            $this->entityManager->remove($decision);
        }

        if (empty($strategy->getDecisionsData())) {
            return;
        }
        $rootDecision = $this->decisionsService->generateDecisionTreeByParamsRecursively($strategy);

        $strategy->addDecision($rootDecision);
    }


    public function getChanceOfExtendingBranch()
    {
        return $this->chanceOfExtendingBranch;
    }

    /**
     * @param Decision $decision
     * @param int $stepsCount
     */
    private function addDecisionsChildrenRecursively(Decision $decision, $stepsCount = 0)
    {
        // Stop condition - when is no steps left
        if ($stepsCount <= 0) {
            return;
        }

        // Minus one step
        $stepsCount--;

        // Create 2 decisions for both partner decisions add add them to children array
        for ($i = 0; $i < 2; $i++) {
            if ($this->faker->boolean($this->chanceOfExtendingBranch)) {
                $child = $this->decisionsService->generateRandomDecision($decision->getStrategy());
                $decision->addChild($child);
                $this->addDecisionsChildrenRecursively($child, $stepsCount);
            }
        }
    }
}