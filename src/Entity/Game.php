<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use phpDocumentor\Reflection\Types\This;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GameRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Game
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GameResult", mappedBy="game", orphanRemoval=true)
     */
    private $gameResults;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $rounds;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $balesForWin;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $balesForLoos;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $balesForCooperation;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $balesForDraw;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="games")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    private $resultsData;

    private $decisionsCount;

    private $sum;

    public function __construct()
    {
        $this->gameResults = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection|GameResult[]
     */
    public function getResults(): Collection
    {
        return $this->gameResults;
    }

    public function addResult(GameResult $gameResult): self
    {
        if (!$this->gameResults->contains($gameResult)) {
            $this->gameResults[] = $gameResult;
            $gameResult->setGame($this);
        }

        return $this;
    }

    public function removeResult(GameResult $gameResult): self
    {
        if ($this->gameResults->contains($gameResult)) {
            $this->gameResults->removeElement($gameResult);
            // set the owning side to null (unless already changed)
            if ($gameResult->getGame() === $this) {
                $gameResult->setGame(null);
            }
        }

        return $this;
    }


    public function setResultsData(array $resultsData): self
    {
        $this->resultsData = $resultsData;
        return $this;
    }

    public function getResultsData(): ?array
    {
        return $this->resultsData;
    }



    // Lifecycle Callbacks

    /**
     * @ORM\PrePersist
     */
    public function beforeCreate()
    {
        if ($this->getDate() === null) {
            $this->setDate(new \DateTime());
        }
    }

    public function getRounds(): ?int
    {
        return $this->rounds;
    }

    public function setRounds(?int $rounds): self
    {
        $this->rounds = $rounds;

        return $this;
    }

    public function getBalesForWin(): ?int
    {
        return $this->balesForWin;
    }

    public function setBalesForWin(?int $balesForWin): self
    {
        $this->balesForWin = $balesForWin;

        return $this;
    }

    public function getBalesForLoos(): ?int
    {
        return $this->balesForLoos;
    }

    public function setBalesForLoos(?int $balesForLoos): self
    {
        $this->balesForLoos = $balesForLoos;

        return $this;
    }

    public function getBalesForCooperation(): ?int
    {
        return $this->balesForCooperation;
    }

    public function setBalesForCooperation(?int $balesForCooperation): self
    {
        $this->balesForCooperation = $balesForCooperation;

        return $this;
    }

    public function getBalesForDraw(): ?int
    {
        return $this->balesForDraw;
    }

    public function setBalesForDraw(?int $balesForDraw): self
    {
        $this->balesForDraw = $balesForDraw;

        return $this;
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

    public function getDecisionsCount(): int
    {
        if ($this->decisionsCount !== null) {
            return $this->decisionsCount;
        }
        // @TODO move this calculating to GameRepository.findAllOrderedByCreatedAtDesc function
        return $this->decisionsCount = $this->getResults()->count();
    }

    public function setDecisionsCount(int $decisionsCount): self
    {
        $this->decisionsCount = $decisionsCount;
        return $this;
    }

    public function getSum(): int
    {
        if ($this->sum !== null) {
            return $this->sum;
        }
        // @TODO move this calculating to GameRepository.findAllOrderedByCreatedAtDesc function
        foreach ($this->getResults() as $result) {
            $this->sum += $result->getResult();
        }
        return $this->sum;
    }

    public function setSum(int $sum): self
    {
        $this->sum = $sum;
        return $this;
    }
}
