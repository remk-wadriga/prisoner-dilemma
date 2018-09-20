<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use App\Entity\Traits\IsEnabledEntity;
use App\Validator;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StrategyRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Strategy
{
    use TimestampableEntity;
    use IsEnabledEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="strategies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Decision", mappedBy="strategy", orphanRemoval=true)
     * @ORM\OrderBy({"step" = "ASC"})
     */
    private $decisions;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $decisionsData = [];

    public function __construct()
    {
        $this->decisions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|Decision[]
     */
    public function getDecisions(): Collection
    {
        return $this->decisions;
    }

    public function addDecision(Decision $decision): self
    {
        if (!$this->decisions->contains($decision)) {
            $this->decisions[] = $decision;
            $decision->setStrategy($this);
        }

        return $this;
    }

    public function removeDecision(Decision $decision): self
    {
        if ($this->decisions->contains($decision)) {
            $this->decisions->removeElement($decision);
            // set the owning side to null (unless already changed)
            if ($decision->getStrategy() === $this) {
                $decision->setStrategy(null);
            }
        }

        return $this;
    }

    public function getDecisionsData(): ?array
    {
        return $this->decisionsData;
    }

    public function setDecisionsData(?array $decisionsData): self
    {
        $this->decisionsData = $decisionsData;
        return $this;
    }

    // Lifecycle Callbacks

    /**
     * @ORM\PrePersist
     */
    public function beforeCreate()
    {
        if ($this->getStatus() === null) {
            $this->setStatus(self::getDefaultStatus());
        }
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTime());
        }
        if ($this->getUpdatedAt() === null) {
            $this->setUpdatedAt(new \DateTime());
        }
    }

    /**
     * @ORM\PreUpdate
     */
    public function beforeUpdate()
    {
        $this->setUpdatedAt(new \DateTime());
    }

    // Public functions

    /**
     * @param Decision[] $decisions
     * @param int $parentID
     * @param int $step
     * @return array
     */
    public function getDecisionsAsArray(&$decisions = null, $parentID = null, $step = 1): array
    {
        $result = [];
        if ($decisions === null) {
            $decisions = $this->getDecisions();
        }
        foreach ($decisions as $index => $decision) {
            if ($decision->getStep() !== $step) {
                continue;
            }
            unset($decisions[$index]);
            $result[] = [
                'id' => $decision->getId(),
                'name' => $decision->getType(),
                'parent' => $parentID,
                'step' => $step,
                'returnStep' => $decision->getReturnStep(),
                'children' => $this->getDecisionsAsArray($decisions, $decision->getId(), $step + 1)
            ];
        }
        return $result;
    }
}
