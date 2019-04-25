<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 25.04.2019
 * Time: 16:38
 */

namespace App\Repository\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AbstractServiceRepository
{
    protected $entityManager;
    protected $container;

    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container)
    {
        $this->entityManager = $entityManager;
        $this->container = $container;
    }

    /**
     * Creates a new QueryBuilder instance that is prepopulated for this entity name.
     *
     * @param string $alias
     * @param string $from
     * @param string $indexBy The index for the from.
     *
     * @return QueryBuilder
     */
    public function createQueryBuilder($alias, $from, $indexBy = null)
    {
        return $this->entityManager->createQueryBuilder()
            ->select($alias)
            ->from($from, $alias, $indexBy);
    }

    protected function getParam($name)
    {
        return $this->container->hasParameter($name) ? $this->container->getParameter($name) : null;
    }

    protected function getFrontendDateTimeFormat()
    {
        $format = $this->getParam('frontend_date_time_format');
        if ($format === null) {
            $format = 'Y-m-d H:i';
        }
        return $format;
    }
}