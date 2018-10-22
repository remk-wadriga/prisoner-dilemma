<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 11.09.2018
 * Time: 15:35
 */

namespace App\Controller;

use App\Entity\Strategy;
use App\Form\StrategyForm;
use App\Security\AccessTokenAuthenticator;
use App\Service\StrategyDecisionsService;
use App\Service\StrategyService;
use App\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class StrategyController extends ControllerAbstract
{
    /**
     * @Route("/", name="app_homepage", methods={"GET"})
     */
    public function list(AccessTokenAuthenticator $authenticator)
    {
        // 1. Get current user
        $user = $authenticator->getCurrentUser();

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
     */
    public function show(Strategy $strategy, StrategyDecisionsService $decisionsService)
    {
        // Get current user
        $user = $this->getUser();
        // Check is current user has permissions for this strategy
        if (!$user->getIsAdmin() && $user->getId() !== $strategy->getUser()->getId()) {
            throw new HttpException('Strategy not found', HttpException::CODE_NOT_FOUND);
        }

        return $this->json($this->strategyInfo($strategy, [
            'decisionsData' => $decisionsService->parseDecisionsData($strategy),
        ]));
    }

    /**
     * @Route("/strategy", name="strategy_create", methods={"POST"})
     */
    public function create(Request $request)
    {
        // Create new strategy
        $strategy = new Strategy();
        // Set current user as strategy owner
        $strategy->setUser($this->getUser());
        // Process request
        $form = $this->createJsonForm(StrategyForm::class, $strategy);
        $this->handleJsonForm($form, $request);

        // Save user entity
        $em = $this->getDoctrine()->getManager();
        $em->persist($strategy);
        $em->flush();

        return $this->json($this->strategyInfo($strategy, ['decisionsData']));
    }

    /**
     * @Route("/strategy/{id}", name="strategy_update", methods={"PUT"})
     */
    public function update(Strategy $strategy, Request $request)
    {
        // Check is current user has permissions for updating the strategy
        $user = $this->getUser();
        if (!$user->getIsAdmin() && $user->getId() !== $strategy->getUser()->getId()) {
            throw new HttpException('Strategy is not found', HttpException::CODE_NOT_FOUND);
        }

        // Process request
        $form = $this->createJsonForm(StrategyForm::class, $strategy, ['action' => StrategyForm::ACTION_UPDATE]);
        $this->handleJsonForm($form, $request);

        dd($_POST);

        // Save user entity
        $em = $this->getDoctrine()->getManager();
        $em->persist($strategy);
        $em->flush();

        return $this->json($this->strategyInfo($strategy, ['decisionsData']));
    }

    /**
     * @Route("/strategy/random", name="strategy_generate_random", methods={"POST"})
     */
    public function generateRandom(Request $request, StrategyService $strategyService, StrategyDecisionsService $decisionsService)
    {
        // Generate random strategy
        $strategy = $strategyService->generateRandomStrategy($this->getUser(), $request->request->get('steps'));

        // Save strategy entity
        $em = $this->getDoctrine()->getManager();
        $em->persist($strategy);
        $em->flush();

        return $this->json($this->strategyInfo($strategy, [
            'decisionsData' => $decisionsService->parseDecisionsData($strategy),
        ]));
    }

    /**
     * @Route("/strategy/{id}", name="strategy_delete", methods={"DELETE"})
     */
    public function delete(Strategy $strategy)
    {
        // Check is current user has permissions for updating the strategy
        $user = $this->getUser();
        if (!$user->getIsAdmin() && $user->getId() !== $strategy->getUser()->getId()) {
            throw new HttpException('Strategy is not found', HttpException::CODE_NOT_FOUND);
        }
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