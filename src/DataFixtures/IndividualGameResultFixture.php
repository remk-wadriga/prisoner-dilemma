<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 08.11.2018
 * Time: 11:34
 */

namespace App\DataFixtures;

use App\Entity\GameResult;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Game;
use App\Entity\IndividualGameResult;

class IndividualGameResultFixture extends AbstractFixture implements DependentFixtureInterface
{
    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(IndividualGameResult::class, 1500, function (IndividualGameResult $individualResult, int $i) use ($manager) {
            /** @var Game $game */
            $game = $this->getRandomReference(Game::class);

            $individualResult->setResult($this->faker->numberBetween(-100, 200));

            foreach ($game->getResults() as $result) {
                $result->addIndividualGameResult($individualResult);
                $result->setResult($result->getResult() + $individualResult->getResult());

                foreach ($result->getIndividualGameResults() as $individualGameResult) {
                    $individualGameResult->setPartner($individualResult->getGameResult()->getStrategy());
                    $individualGameResult->setPartnerResult($individualResult->getResult());

                    $manager->persist($individualGameResult);
                }

                if ($individualResult->getPartner() === null) {
                    $individualResult->setPartner($result->getStrategy());
                    $individualResult->setPartnerResult(0);
                }

                $manager->persist($result);
            }

            if ($individualResult->getPartner() === null) {
                /** @var GameResult $result */
                $result = $this->getRandomReference(GameResult::class);
                $individualResult->setPartner($result->getStrategy());
                $individualResult->setPartnerResult(0);
            }
        });

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameResultFixture::class,
        ];
    }
}