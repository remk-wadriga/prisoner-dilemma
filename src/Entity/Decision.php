<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Types\Enum\DecisionTypeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DecisionRepository")
 */
class Decision
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Strategy", inversedBy="decisions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $strategy;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Decision", inversedBy="children")
     */
    private $parent;

    /**
     * @ORM\Column(type="string", length=6)
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Decision", mappedBy="parent")
     */
    private $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStrategy(): ?Strategy
    {
        return $this->strategy;
    }

    public function setStrategy(?Strategy $strategy): self
    {
        $this->strategy = $strategy;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        if (!in_array($type, DecisionTypeEnum::getAvailableTypes())) {
            throw new \InvalidArgumentException('Invalid type');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|Decision[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }
}
