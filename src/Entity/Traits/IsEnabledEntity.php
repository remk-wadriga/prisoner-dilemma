<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 11.09.2018
 * Time: 14:50
 */

namespace App\Entity\Traits;

use App\Entity\Types\Enum\IsEnabledEnum;
use Doctrine\ORM\Mapping as ORM;

trait IsEnabledEntity
{
    /**
     * @var string
     * @ORM\Column(name="status", type="string", length=8, nullable=false)
     */
    protected $status;

    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): self
    {
        if (!in_array($status, IsEnabledEnum::getAvailableTypes())) {
            throw new \InvalidArgumentException('Invalid type');
        }

        $this->status = $status;

        return $this;
    }

    public function getStatusName(): string
    {
        return IsEnabledEnum::getTypeName($this->getStatus());
    }

    public static function getDefaultStatus()
    {
        return IsEnabledEnum::TYPE_ENABLED;
    }
}