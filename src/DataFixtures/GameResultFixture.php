<?php

namespace App\DataFixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Game;
use App\Entity\GameResult;
use App\Entity\Strategy;

class GameResultFixture extends AbstractFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(GameResult::class, 100, function (GameResult $result, int $i) use ($manager) {
            /** @var Game $game */
            $game = $this->getRandomReference(Game::class);
            /** @var Strategy[] $strategies */
            $strategies = $this->getRandomReferences(Strategy::class, 50);

            $result->setGame($game);

            foreach ($strategies as $index => $strategy) {
                if ($strategy->getUser() == $game->getUser()) {
                    $result->setStrategy($strategy);
                    break;
                }
            }

            $result->setResult(0);
        });

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            StrategyFixture::class,
            GameFixture::class,
        ];
    }
}
