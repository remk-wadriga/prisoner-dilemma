<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\IndividualGameResultRepository")
 */
class IndividualGameResult
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GameResult", inversedBy="individualGameResults")
     * @ORM\JoinColumn(nullable=false)
     */
    private $gameResult;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Strategy", inversedBy="individualGameResults")
     * @ORM\JoinColumn(nullable=false)
     */
    private $partner;

    /**
     * @ORM\Column(type="integer")
     */
    private $result;

    /**
     * @ORM\Column(type="integer")
     */
    private $partnerResult;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGameResult(): ?GameResult
    {
        return $this->gameResult;
    }

    public function setGameResult(?GameResult $gameResult): self
    {
        $this->gameResult = $gameResult;

        return $this;
    }

    public function getPartner(): ?Strategy
    {
        return $this->partner;
    }

    public function setPartner(?Strategy $partner): self
    {
        $this->partner = $partner;

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

    public function getPartnerResult(): ?int
    {
        return $this->partnerResult;
    }

    public function setPartnerResult(int $partnerResult): self
    {
        $this->partnerResult = $partnerResult;

        return $this;
    }
}
