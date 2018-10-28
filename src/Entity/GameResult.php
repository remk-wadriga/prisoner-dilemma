<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GameResultRepository")
 */
class GameResult
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game", inversedBy="gameResults")
     * @ORM\JoinColumn(nullable=false)
     */
    private $game;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Strategy", inversedBy="gameResults")
     * @ORM\JoinColumn(nullable=false)
     */
    private $strategy;

    /**
     * @ORM\Column(type="integer")
     */
    private $result;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\IndividualGameResult", mappedBy="gameResult", orphanRemoval=true)
     */
    private $individualGameResults;

    public function __construct()
    {
        $this->individualGameResults = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): self
    {
        $this->game = $game;

        return $this;
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

    public function getResult(): ?int
    {
        return $this->result;
    }

    public function setResult(int $result): self
    {
        $this->result = $result;

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
            $individualGameResult->setGameResult($this);
        }

        return $this;
    }

    public function removeIndividualGameResult(IndividualGameResult $individualGameResult): self
    {
        if ($this->individualGameResults->contains($individualGameResult)) {
            $this->individualGameResults->removeElement($individualGameResult);
            // set the owning side to null (unless already changed)
            if ($individualGameResult->getGameResult() === $this) {
                $individualGameResult->setGameResult(null);
            }
        }

        return $this;
    }
}
