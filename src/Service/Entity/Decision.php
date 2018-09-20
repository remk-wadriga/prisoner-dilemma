<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 21.09.2018
 * Time: 00:27
 */

namespace App\Service\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Strategy;
use Faker\Factory;

class Decision extends EntityAbstract
{
    private $id;

    /**
     * @var Strategy
     */
    private $strategy;

    private $parent;

    private $children;

    private $step;

    private $return_step;

    private $type;

    public function __construct()
    {
        $this->id = Factory::create()->numberBetween(1, 1000000000);
        $this->children = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setStrategy(Strategy $strategy): self
    {
        $this->strategy = $strategy;
        return $this;
    }
    public function getStrategy(): Strategy
    {
        return $this->strategy;
    }

    public function setParent(?Decision $parent): self
    {
        $this->parent = $parent;
        return $this;
    }
    public function getParent(): ?Decision
    {
        return $this->parent;
    }

    /**
     * @return Collection|Decision[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Decision $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(Decision $child): self
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function setStep(?int $step): self
    {
        $this->step = $step;
        return $this;
    }
    public function getStep(): ?int
    {
        return $this->step;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }
    public function getType(): string
    {
        return $this->type;
    }

    public function setReturnStep(?int $returnStep): self
    {
        $this->return_step = $returnStep;
        return $this;
    }
    public function getReturnStep(): ?int
    {
        return $this->return_step;
    }
}