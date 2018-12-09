<?php

namespace App\DataFixtures;

use App\Entity\Strategy;
use App\Entity\Types\Enum\IsEnabledEnum;
use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class StrategyFixture extends AbstractFixture  implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(Strategy::class, 60, function (Strategy $strategy, int $i) use ($manager) {
            /** @var \App\Entity\User $user */
            $user = $this->getRandomReference(User::class);
            $strategy
                ->setUser($user)
                ->setName($this->faker->name)
                ->setDescription($this->faker->text)
                ->setStatus($this->faker->boolean(70) ? IsEnabledEnum::TYPE_ENABLED : IsEnabledEnum::TYPE_DISABLED)
            ;
        });

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixture::class,
        ];
    }
}
