<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 07.11.2018
 * Time: 22:28
 */

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Game;

class GameFixture extends AbstractFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(Game::class, 20, function (Game $game, int $i) use ($manager) {
            /** @var \App\Entity\User $user */
            $user = $this->getRandomReference(User::class);
            $game
                ->setName($this->faker->name)
                ->setDescription($this->faker->text)
                ->setRounds($this->faker->numberBetween(5, 30))
                ->setBalesForWin($this->faker->numberBetween(5, 40))
                ->setBalesForLoos($this->faker->numberBetween(-20, 0))
                ->setBalesForCooperation($this->faker->numberBetween(0, 15))
                ->setBalesForDraw($this->faker->numberBetween(5, 20))
                ->setUser($user)
            ;
        });

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixture::class,
            StrategyFixture::class,
        ];
    }
}