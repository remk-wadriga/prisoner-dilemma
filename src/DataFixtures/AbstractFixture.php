<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 06.09.2018
 * Time: 12:58
 */

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

abstract class AbstractFixture extends Fixture
{
    /** @var ObjectManager */
    private $manager;

    /** @var \Faker\Generator */
    protected $faker;

    protected $isEnabled = true;

    private $referencesIndex = [];

    private $filteredReferencesIndex = [];

    abstract protected function loadData(ObjectManager $em);

    public function load(ObjectManager $manager)
    {
        if (!$this->isEnabled) {
            return;
        }

        $this->manager = $manager;
        $this->faker = Factory::create();

        $this->loadData($manager);
    }

    protected function createMany(string $className, int $count, callable $factory, $index = 0)
    {
        for ($i = 0; $i < $count; $i++) {
            $entity = new $className();
            $factory($entity, $i);
            $this->manager->persist($entity);
            // store for usage later as App\Entity\ClassName_#COUNT#
            $this->addReference($className . '_' . $index . '_' . $i, $entity);
        }
    }

    protected function getRandomReference(string $className, $conditions = [])
    {
        if (!isset($this->filteredReferencesIndex[$className])) {
            $this->filteredReferencesIndex[$className] = [];
        }
        if (!isset($this->referencesIndex[$className])) {
            $this->referencesIndex[$className] = [];
            foreach ($this->referenceRepository->getReferences() as $key => $ref) {
                if (strpos($key, $className . '_') === 0) {
                    $this->referencesIndex[$className][] = $key;
                    if (!empty($conditions)) {
                        foreach ($conditions as $attr => $value) {
                            $getter = 'get' . ucfirst($attr);
                            if (method_exists($ref, $getter) && $ref->$getter() == $value) {
                                $this->filteredReferencesIndex[$className][] = $key;
                            }
                        }
                    }
                }
            }
        }
        if (empty($this->referencesIndex[$className])) {
            throw new \Exception(sprintf('Cannot find any references for class "%s"', $className));
        }
        if (empty($conditions)) {
            $randomReferenceKey = $this->faker->randomElement($this->referencesIndex[$className]);
        } elseif (!empty($this->filteredReferencesIndex[$className])) {
            $randomReferenceKey = $this->faker->randomElement($this->filteredReferencesIndex[$className]);
        } else {
            return null;
        }

        return $this->getReference($randomReferenceKey);
    }

    protected function getRandomReferences(string $className, int $count)
    {
        $references = [];
        while (count($references) < $count) {
            $references[] = $this->getRandomReference($className);
        }
        return $references;
    }
}