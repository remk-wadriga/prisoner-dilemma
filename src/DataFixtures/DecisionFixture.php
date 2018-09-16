<?php

namespace App\DataFixtures;

use App\Entity\Decision;
use App\Entity\Strategy;
use App\Entity\Types\Enum\DecisionTypeEnum;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class DecisionFixture extends AbstractFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager)
    {
        /** @var Decision[] $decisions */
        $decisions = [];
        $createPart = function ($index) use ($manager, &$decisions) {
            $step = 1;
            $this->createMany(Decision::class, 20, function (Decision $decision, int $i) use ($manager, &$step, &$decisions) {
                /** @var \App\Entity\Strategy $strategy */
                $strategy = $this->getRandomReference(Strategy::class);
                if ($i > 0 && $this->faker->boolean(80)) {
                    $returnStep = null;
                } else {
                    $parent = null;
                    $step = 1;
                    $returnStep = $this->faker->randomElement([1, 2, 3]);
                }
                $decision
                    ->setStrategy($strategy)
                    ->setStep($step)
                    ->setReturnStep($returnStep)
                    ->setType($this->faker->randomElement(DecisionTypeEnum::getAvailableTypes()))
                ;
                $decisions[] = $decision;
                $step++;
            }, $index);
        };

        for ($i = 0; $i < 50; $i++) {
            $createPart($i);
        }

        $manager->flush();

        foreach ($decisions as $decision) {
            if ($decision->getStep() > 1) {
                /** @var Decision $parent */
                $parent = $this->getRandomReference(Decision::class, ['step' => $decision->getStep() - 1]);
                if ($parent->getId() === $decision->getId()) {
                    $parent = $this->getRandomReference(Decision::class, ['step' => $decision->getStep() - 1]);
                    if ($parent->getId() === $decision->getId()) {
                        $parent = $this->getRandomReference(Decision::class, ['step' => $decision->getStep() - 1]);
                    }
                }
                if ($parent->getId() === $decision->getId()) {
                    continue;
                }
                $decision->setParent($parent);
                $manager->persist($decision);
            }
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            StrategyFixture::class,
        ];
    }


}
