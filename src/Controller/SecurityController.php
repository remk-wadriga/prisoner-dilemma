<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 06.09.2018
 * Time: 15:15
 */

namespace App\Controller;

use App\Security\AccessTokenUserProvider;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends AbstractController
{
    private $usrProvider;

    public function __construct(AccessTokenUserProvider $userProvider)
    {
        $this->usrProvider = $userProvider;
    }

    /**
     * @Route("/login", name="security_login", methods={"POST"})
     */
    public function login(Request $request)
    {
        return $this->json($this->usrProvider->loginUser($request)->toApi());
    }

    /**
     * @Route("/renew-token", name="security_renew_token", methods={"POST"})
     */
    public function renewToken(Request $request)
    {
        print_r($this->getUser()); echo "\n"; exit();
    }
}