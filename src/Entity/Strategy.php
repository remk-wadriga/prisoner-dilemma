<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use App\Entity\Traits\IsEnabledEntity;
use App\Service\Entity\Decision;

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
     * @ORM\Column(type="json", nullable=true)
     */
    private $decisionsData = [];

    private $decisions = [];

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
     * @return Decision[]
     */
    public function getDecisions(): array
    {
        return array_values($this->decisions);
    }

    public function addDecision(Decision $decision): self
    {
        $this->decisions[$decision->getId()] = $decision;
        $decision->setStrategy($this);
        return $this;
    }

    public function removeDecision(Decision $decision): self
    {
        if (isset($this->decisions[$decision->getId()])) {
            unset($this->decisions[$decision->getId()]);
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
}
