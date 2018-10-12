<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 20.09.2018
 * Time: 10:39
 */

namespace App\Service;

use App\Entity\Strategy;
use App\Service\Entity\Decision;
use App\Exception\ServiceException;
use App\Entity\Types\Enum\DecisionTypeEnum;
use Doctrine\ORM\EntityManagerInterface;

class StrategyDecisionsService extends AbstractService
{
    const PORT_TYPE_IN = 'in';
    const PORT_TYPE_OUT = 'out';

    static $allowedPortTypes = [self::PORT_TYPE_IN, self::PORT_TYPE_OUT];

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Strategy $strategy
     * @throws ServiceException
     */
    public function parseDecisionsData(Strategy $strategy)
    {

    }
}