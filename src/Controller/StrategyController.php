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
use Mcfedr\JsonFormBundle\Controller\JsonController;
use App\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class StrategyController extends JsonController
{
    /**
     * @Route("/", name="app_homepage")
     */
    public function list(AccessTokenAuthenticator $authenticator)
    {
        // 1. Get current user
        $user = $authenticator->getCurrentUser();

        if ($user->getIsAdmin()) {
            $strategies = $this->getDoctrine()->getRepository(Strategy::class)->findAll();
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
     * @Route("/strategy/create", name="strategy_create", methods={"PUT"})
     */
    public function create(Request $request, AccessTokenAuthenticator $authenticator)
    {
        // Create new strategy
        $strategy = new Strategy();
        // Set current user as strategy owner
        $strategy->setUser($authenticator->getCurrentUser());
        // Process request
        $form = $this->createJsonForm(StrategyForm::class, $strategy);
        $this->handleJsonForm($form, $request);

        // Save user entity
        $em = $this->getDoctrine()->getManager();
        $em->persist($strategy);
        $em->flush();

        return $this->json($this->strategyInfo($strategy));
    }

    /**
     * @Route("/strategy/{id}", name="strategy_show")
     */
    public function show(Strategy $strategy, AccessTokenAuthenticator $authenticator)
    {
        // Get current user
        $user = $authenticator->getCurrentUser();
        // Check is current user has permissions for this strategy
        if (!$user->getIsAdmin() && $user->getId() !== $strategy->getUser()->getId()) {
            throw new HttpException('Strategy not found', HttpException::CODE_NOT_FOUND);
        }
        return $this->json($this->strategyInfo($strategy));
    }


    protected function strategyInfo(Strategy $strategy)
    {
        return [
            'id' => $strategy->getId(),
            'name' => $strategy->getName(),
            'description' => $strategy->getDescription(),
            'status' => $strategy->getStatusName(),
        ];
    }
}