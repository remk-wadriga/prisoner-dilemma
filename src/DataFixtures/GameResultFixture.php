<?php

namespace App\DataFixtures;

use App\Entity\IndividualGameResult;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Game;
use App\Entity\GameResult;
use App\Entity\Strategy;

class GameResultFixture extends AbstractFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager)
    {
        $this->generateGameResults($manager);
        $this->generateIndividualGameResults($manager);
    }

    public function getDependencies()
    {
        return [
            StrategyFixture::class,
            GameFixture::class,
        ];
    }


    /**
     * @param ObjectManager $manager
     */
    private function generateGameResults(ObjectManager $manager)
    {
        /** @var Game[] $games */
        $games = $manager->getRepository(Game::class)->findAll();

        // Create game results
        foreach ($games as $game) {
            $strategiesCount = $manager->getRepository(Strategy::class)->count(['user' => $game->getUser()]);

            $limit = $this->faker->numberBetween(2, 10);

            /** @var Strategy[] $strategies */
            $strategies = $manager->getRepository(Strategy::class)
                ->createQueryBuilder('s')
                ->andWhere('s.user = :user_id')
                ->setParameter('user_id', $game->getUser()->getId())
                ->setMaxResults($limit)
                ->setFirstResult($this->faker->numberBetween(0, $strategiesCount - $limit))
                ->getQuery()
                ->getResult();
            if (count($strategies) < 2) {
                continue;
            }

            foreach ($strategies as $index => $strategy) {
                $gameResult = new GameResult();
                $gameResult
                    ->setGame($game)
                    ->setStrategy($strategy)
                    ->setResult(0);
                $manager->persist($gameResult);
            }
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    private function generateIndividualGameResults(ObjectManager $manager)
    {
        /** @var Game[] $games */
        $games = $manager->getRepository(Game::class)->findAll();

        foreach ($games as $game) {
            /** @var GameResult[] $gameResults */
            $gameResults = $game->getResults()->toArray();
            $this->generateIndividualResultsRecursively($gameResults);

            foreach ($gameResults as $gameResult) {
                foreach ($gameResult->getIndividualGameResults() as $individualGameResult) {
                    $gameResult->setResult($gameResult->getResult() + $individualGameResult->getResult());
                    $manager->persist($individualGameResult);
                }
                $manager->persist($gameResult);
            }
        }

        $manager->flush();
    }

    /**
     * @param GameResult[] $gameResults
     * @param GameResult $currentResult
     */
    private function generateIndividualResultsRecursively(array $gameResults, GameResult $currentResult = null)
    {
        if ($currentResult === null) {
            $currentResult = current($gameResults);
        }

        if ($currentResult->getIndividualGameResults()->count() === count($gameResults) - 1) {
            return;
        }

        foreach ($gameResults as $gameResult) {
            if ($gameResult == $currentResult) {
                continue;
            }

            $individualResult = new IndividualGameResult();
            $individualResult
                ->setPartner($gameResult->getStrategy())
                ->setResult($this->faker->numberBetween(-30, 60))
                ->setPartnerResult($this->faker->numberBetween(-30, 60))
            ;

            $currentResult->addIndividualGameResult($individualResult);
        }

        $nextResult = next($gameResults);
        if ($nextResult === false) {
            return;
        }
        $this->generateIndividualResultsRecursively($gameResults, $nextResult);
    }
}
