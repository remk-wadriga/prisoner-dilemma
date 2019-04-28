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
     * @ORM\OneToMany(targetEntity="App\Entity\Decision", mappedBy="strategy", cascade={"persist", "remove"})
     */
    private $decisions;

    /**
     * @Validator\Type(message="Param decisionsData must be an array", type="array")
     */
    private $decisionsData;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GameResult", mappedBy="strategy", orphanRemoval=true)
     */
    private $gameResults;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\IndividualGameResult", mappedBy="partner", orphanRemoval=true)
     */
    private $individualGameResults;

    public function __construct()
    {
        $this->decisions = new ArrayCollection();
        $this->gameResults = new ArrayCollection();
        $this->individualGameResults = new ArrayCollection();
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
        }

        return $this;
    }

    public function getDecisionsData(): ?array
    {
        return $this->decisionsData;
    }

    public function setDecisionsData(array $decisionsData): self
    {
        $this->decisionsData = $decisionsData;
        return $this;
    }

    /**
     * @return Collection|GameResult[]
     */
    public function getGameResults(): Collection
    {
        return $this->gameResults;
    }

    public function addGameResult(GameResult $gameResult): self
    {
        if (!$this->gameResults->contains($gameResult)) {
            $this->gameResults[] = $gameResult;
            $gameResult->setStrategy($this);
        }

        return $this;
    }

    public function removeGameResult(GameResult $gameResult): self
    {
        if ($this->gameResults->contains($gameResult)) {
            $this->gameResults->removeElement($gameResult);
            // set the owning side to null (unless already changed)
            if ($gameResult->getStrategy() === $this) {
                $gameResult->setStrategy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|IndividualGameResult[]
     */
    public function getIndividualGameResults(): Collection
    {
        return $this->individualGameResults;
    }

    public function addIndividualGameResult(IndividualGameResult $individualGameResult): self
    {
        if (!$this->individualGameResults->contains($individualGameResult)) {
            $this->individualGameResults[] = $individualGameResult;
            $individualGameResult->setPartner($this);
        }

        return $this;
    }

    public function removeIndividualGameResult(IndividualGameResult $individualGameResult): self
    {
        if ($this->individualGameResults->contains($individualGameResult)) {
            $this->individualGameResults->removeElement($individualGameResult);
            // set the owning side to null (unless already changed)
            if ($individualGameResult->getPartner() === $this) {
                $individualGameResult->setPartner(null);
            }
        }

        return $this;
    }


}
