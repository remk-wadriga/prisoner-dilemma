<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 06.09.2018
 * Time: 15:15
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\UserForm;
use App\Security\AccessTokenUserProvider;
use Mcfedr\JsonFormBundle\Controller\JsonController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Security\AccessTokenAuthenticator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends JsonController
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
     * @Route("/logout", name="security_logout", methods={"POST"})
     */
    public function logout(AccessTokenAuthenticator $authenticator)
    {
        // 1. Get current user
        $user = $authenticator->getCurrentUser();
        // 2. Logout user
        $this->usrProvider->logoutUser($user);
        // 3. Say "OK"
        return $this->json('OK');
    }

    /**
     * @Route("/renew-token", name="security_renew_token", methods={"POST"})
     */
    public function renewToken(AccessTokenAuthenticator $authenticator)
    {
        return $this->json($authenticator->getAccessToken()->toApi());
    }

    /**
     * @Route("/registration", name="security_registration", methods={"POST"})
     */
    public function registration(Request $request, UserPasswordEncoderInterface $passwordEncoder, AccessTokenUserProvider $userProvider)
    {
        // Create form and handle data
        $user = new User();
        $form = $this->createJsonForm(UserForm::class, $user);
        $this->handleJsonForm($form, $request);
        $user->setPassword($passwordEncoder->encodePassword($user, $user->getPlainPassword()));

        // Save user entity
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        // Return new user access token
        return $this->json($userProvider->getAccessTokenForUser($user)->toApi());
    }

    /**
     * @Route("/user-info", name="security_user_info")
     */
    public function userInfo(AccessTokenAuthenticator $authenticator)
    {
        $user = $authenticator->getCurrentUser();
        return $this->json([
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email' => $user->getEmail(),
        ]);
    }
}