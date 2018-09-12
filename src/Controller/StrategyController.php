<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 11.09.2018
 * Time: 15:35
 */

namespace App\Controller;

use App\Entity\Strategy;
use App\Entity\User;
use App\Form\StrategyForm;
use App\Security\AccessTokenAuthenticator;
use Mcfedr\JsonFormBundle\Controller\JsonController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class StrategyController extends JsonController
{
    /**
     * @Route("/", name="app_homepage")
     */
    public function list()
    {
        return $this->json([
            ['name' => 'Strategy 1'],
            ['name' => 'Strategy 2'],
            ['name' => 'Strategy 3'],
            ['name' => 'Strategy 4'],
            ['name' => 'Strategy 5'],
        ]);
    }

    /**
     * @Route("/strategy/create", name="strategy_create", methods={"GET"})
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


    protected function strategyInfo(Strategy $strategy)
    {
        return [
            'name' => $strategy->getName(),
            'description' => $strategy->getDescription(),
            'status' => $strategy->getStatusName(),
        ];
    }
}