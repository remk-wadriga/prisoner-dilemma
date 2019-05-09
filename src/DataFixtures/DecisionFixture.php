<?php

namespace App\DataFixtures;

use App\Entity\Types\Enum\DecisionTypeEnum;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Decision;
use App\Entity\Strategy;

class DecisionFixture extends AbstractFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(Decision::class, 1000, function (Decision $decision, int $i) use ($manager) {
            $decision->setType($this->faker->randomElement(DecisionTypeEnum::getAvailableTypes()));

            /** @var \App\Entity\Strategy $strategy */
            $strategy = $this->getRandomReference(Strategy::class);

            $decisions = [];
            foreach ($strategy->getDecisions() as $dec) {
                if ($dec != $decision && $dec->getChildren()->count() < 2) {
                    $decisions[] = $dec;
                }
            }
            if (!empty($decisions)) {
                /** @var \App\Entity\Decision $parent */
                $parent = $this->faker->randomElement($decisions);
                $parent->addChild($decision);
            }

            $strategy->addDecision($decision);
        });

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            StrategyFixture::class,
        ];
    }
}
