<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 11.09.2018
 * Time: 15:35
 */

namespace App\Controller;

use App\Entity\Strategy;
use App\Exception\StrategyServiceException;
use App\Form\StrategyForm;
use App\Service\StrategyDecisionsService;
use App\Service\StrategyService;
use App\Exception\HttpException;
use Mcfedr\JsonFormBundle\Exception\InvalidFormHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class StrategyController extends ControllerAbstract
{
    /**
     * @Route("/", name="app_homepage", methods={"GET"})
     */
    public function list()
    {
        // Get current user
        $user = $this->getUser();

        if ($user->getIsAdmin()) {
            $strategies = $this->getDoctrine()->getRepository(Strategy::class)->findAllOrderedByCreatedAtDesc();
        } else {
            $strategies = $user->getStrategies();
        }
        $list = [];
        foreach ($strategies as $strategy) {
            $list[] = $this->strategyInfo($strategy);
        }
        return $this->json($list);
    }

    /**
     * @Route("/strategy/{id}", name="strategy_show", methods={"GET"}, requirements={"id"="\d+"})
     * @IsGranted("MANAGE", subject="strategy")
     */
    public function show(Strategy $strategy, StrategyDecisionsService $decisionsService)
    {
        // Get current user
        $user = $this->getUser();
        // Check is current user has permissions for this strategy
        if (!$user->getIsAdmin() && $user->getId() !== $strategy->getUser()->getId()) {
            throw new HttpException('Strategy not found', HttpException::CODE_NOT_FOUND);
        }

        // Build strategy decisions tree
        try {
            $decisionsData = $decisionsService->createDecisionsDataArray($strategy);
        } catch (StrategyServiceException $e) {
            throw new HttpException('Can\'t build strategy decisions tree', 0, $e);
        }

        return $this->json($this->strategyInfo($strategy, [
            'decisionsData' => $decisionsData,
        ]));
    }

    /**
     * @Route("/strategy", name="strategy_create", methods={"POST"})
     */
    public function create(Request $request, StrategyService $strategyService, StrategyDecisionsService $decisionsService)
    {
        // Create new strategy
        $strategy = new Strategy();
        // Set current user as strategy owner
        $strategy->setUser($this->getUser());
        // Process request
        $form = $this->createJsonForm(StrategyForm::class, $strategy);
        $this->handleJsonForm($form, $request);

        // Parse decisions data - it means create new decisions tree and add it to strategy
        try {
            $strategyService->parseDecisionsData($strategy);
        } catch (StrategyServiceException $e) {
            throw new HttpException('Can\'t parse decision data', 0, $e);
        }

        // Save user entity
        $em = $this->getDoctrine()->getManager();
        $em->persist($strategy);
        $em->flush();

        // Build strategy decisions tree
        try {
            $decisionsData = $decisionsService->createDecisionsDataArray($strategy);
        } catch (StrategyServiceException $e) {
            throw new HttpException('Can\'t build strategy decisions tree', 0, $e);
        }

        return $this->json($this->strategyInfo($strategy, [
            'decisionsData' => $decisionsData,
        ]));
    }

    /**
     * @Route("/strategy/{id}", name="strategy_update", methods={"PUT"})
     * @IsGranted("MANAGE", subject="strategy")
     */
    public function update(Strategy $strategy, Request $request, StrategyService $strategyService, StrategyDecisionsService $decisionsService)
    {
        // Process request
        $form = $this->createJsonForm(StrategyForm::class, $strategy, ['action' => StrategyForm::ACTION_UPDATE]);
        $this->handleJsonForm($form, $request);

        // Parse decisions data - it means delete old strategy decisions tree,
        //  create new decisions tree and add it to strategy
        try {
            $strategyService->parseDecisionsData($strategy);
        } catch (StrategyServiceException $e) {
            throw new HttpException('Can\'t parse decision data', 0, $e);
        }

        // Save user entity
        $em = $this->getDoctrine()->getManager();
        $em->persist($strategy);
        $em->flush();

        return $this->json($this->strategyInfo($strategy));
    }

    /**
     * @Route("/strategy/random", name="strategy_generate_random", methods={"POST"})
     */
    public function generateRandom(Request $request, StrategyService $strategyService)
    {
        // Generate random strategy
        $strategy = $strategyService->generateRandomStrategy($this->getUser(),
            $request->request->get('steps'),
            $request->request->get('name'),
            $request->request->get('extendingChance'),
            $request->request->get('randomDecisionChance'),
            $request->request->get('copyDecisionChance'),
            $request->request->get('acceptDecisionChance')
        );

        // Save strategy entity
        $em = $this->getDoctrine()->getManager();
        $em->persist($strategy);
        $em->flush();

        return $this->json($this->strategyInfo($strategy));
    }

    /**
     * @Route("/strategy/{id}", name="strategy_delete", methods={"DELETE"})
     * @IsGranted("MANAGE", subject="strategy")
     */
    public function delete(Strategy $strategy)
    {
        // Try to delete strategy
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($strategy);
        $manager->flush();
        return $this->json('OK');
    }


    protected function strategyInfo(Strategy $strategy, array $additionalFields = []): array
    {
        $params = [
            'id' => $strategy->getId(),
            'name' => $strategy->getName(),
            'description' => $strategy->getDescription(),
            'status' => $strategy->getStatus(),
        ];

        foreach ($additionalFields as $index => $field) {
            if (is_array($field)) {
                $params[$index] = $field;
            } else {
                $getter = 'get' . ucfirst($field);
                if (method_exists($strategy, $getter)) {
                    $params[$field] = $strategy->$getter();
                }
            }
        }

        return $params;
    }
}