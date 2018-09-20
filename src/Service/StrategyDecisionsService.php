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
        // Check data
        $data = $strategy->getDecisionsData();
        if (empty($data)) {
            $strategy->setDecisionsData([]);
            return;
        }
        if (!is_array($data)
            || !isset($data['links']) || !is_array($data['links'])
            || !isset($data['nodes']) || !is_array($data['nodes'])
        ) {
            throw new ServiceException(
                'Invalid param "decisionsData" value. It should bee something like ["links": [...], "nodes": [...]]',
                ServiceException::CODE_INVALID_PARAMS);
        }

        // Setup links (find node id for each of them)
        $links = [];
        foreach ($data['links'] as $link) {
            // Check link
            if (!is_array($link) || !isset($link['from']) || (!is_integer($link['from']) && !is_string($link['from']))
                || !isset($link['to']) || (!is_integer($link['to']) && !is_string($link['to']))
            ) {
                throw new ServiceException(
                    'Invalid param "decisionsData.links" value. It should bee something like [["from": 123, "to": 321, ...], ...]',
                    ServiceException::CODE_INVALID_PARAMS);
            }
            $links[$link['from']] = $link['to'];
        }

        // Setup nodes (find type, id and children ids for each of them)
        $nodes = [];
        foreach ($data['nodes'] as $node) {
            // Check node
            if (!isset($node['title']) || !is_string($node['title'])
                || !isset($node['ports']) || !is_array($node['ports'])
            ) {
                throw new ServiceException(
                    'Invalid param "decisionsData.nodes" value. It should bee something like [["title": "Accept", "ports": [...]], ...]',
                    ServiceException::CODE_INVALID_PARAMS);
            }
            // Try to get node type
            $type = DecisionTypeEnum::getTypeByName($node['title']);
            if ($type === null) {
                throw new ServiceException(
                    sprintf('Invalid param "decisionsData.nodes[].title" value. Allowed values: %s',
                        '"' . implode('", "', DecisionTypeEnum::getAvailableTypesNames()) . '"'),
                    ServiceException::CODE_INVALID_PARAMS);
            }

            // Set node base values
            $node['id'] = 0;
            $node['type'] = $type;
            $node['children'] = [];

            // Try to find "in" port for each node and set it`s id as node id
            foreach ($node['ports'] as $port) {
                // Check port
                if (!is_array($port) || !isset($port['id']) || (!is_integer($port['id']) && !is_string($port['id']))
                    || !isset($port['type']) || !is_string($port['type'])
                ) {
                    throw new ServiceException(
                        'Invalid param "decisionsData.nodes[].ports" value. It should bee something like [["id": 123, "type": "out"], ...]',
                        ServiceException::CODE_INVALID_PARAMS);
                }
                // Check port type
                if (!in_array($port['type'], self::$allowedPortTypes)) {
                    throw new ServiceException(
                        sprintf('Invalid param "decisionsData.nodes[].ports[].type" value. Allowed values: %s',
                            '"' . implode('", "', self::$allowedPortTypes) . '"'),
                        ServiceException::CODE_INVALID_PARAMS);
                }

                // Set node id and children ID
                if ($port['type'] === self::PORT_TYPE_IN) {
                    $node['id'] = $port['id'];
                } elseif ($port['type'] === self::PORT_TYPE_OUT) {
                    if (!isset($links[$port['id']])) {
                        continue;
                    }
                    $childID = $links[$port['id']];
                    if (!in_array($childID, $node['children'])) {
                        $node['children'][] = $childID;
                    }
                }
            }

            // Add node to result array by it`s id
            $nodes[$node['id']] = [
                'type' => $node['type'],
                'children' => $node['children'],
            ];
        }

        // Create Decision object for each node
        /** @var Decision[] $decisions */
        $decisions = [];
        foreach ($nodes as $id => $node) {
            if (isset($decisions[$id])) {
                continue;
            }
            $decision = new Decision();
            $decision->setType($node['type']);
            $decisions[$id] = $decision;
        }

        // Set step numbers and parents for all nodes
        $this->setStepAndParentToDecisionsRecursively($decisions, $nodes);

        // Add new decisions to strategy
        foreach ($decisions as $decision) {
            $strategy->addDecision($decision);
        }
    }


    /**
     * @param Decision[] $decisions
     * @param array $nodes
     * @param int $step
     */
    protected function setStepAndParentToDecisionsRecursively(array $decisions, array $nodes, int $step = 1)
    {
        if (empty($nodes)) {
            return;
        }
        foreach ($nodes as $id => $node) {
            if (!isset($decisions[$id])) {
                continue;
            }
            $decision = $decisions[$id];
            if ($decision->getStep() === null) {
                $decision->setStep($step);
            }
            if (!empty($node['children'])) {
                $children = [];
                foreach ($node['children'] as $childID) {
                    if (!isset($nodes[$childID])) {
                        continue;
                    }
                    if (!isset($decisions[$childID])) {
                        continue;
                    }
                    $decision->addChild($decisions[$childID]);
                    $children[$childID] = $nodes[$childID];
                }
                $this->setStepAndParentToDecisionsRecursively($decisions, $children, $step + 1);
            }
        }
    }
}