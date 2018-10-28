<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GameRepository")
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
    public function getGameResults(): Collection
    {
        return $this->gameResults;
    }

    public function addGameResult(GameResult $gameResult): self
    {
        if (!$this->gameResults->contains($gameResult)) {
            $this->gameResults[] = $gameResult;
            $gameResult->setGame($this);
        }

        return $this;
    }

    public function removeGameResult(GameResult $gameResult): self
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
}
