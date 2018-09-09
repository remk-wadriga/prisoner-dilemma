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
        /*return $this->json([
            'access_token' => 'OWRhY2NiNDMwNGFmNzc5NDNjNmM0OGIwMzIzNTUxMjI2NmU2OThlYWVlOGRkZWZjZTY3ODIwNDczMWJlMGIyZg==',
            'renew_token' => 'Njg3MTY0NTBlOTllZjNkMTYyN2VhMTUwZmUxYTRhZjhmOWFiZGVmNzI2OWQ1ZmU2NzE0NzMzMjEzYjRlY2Y2MA==',
            'expired_at' => '2018-12-08 23:15:06',
        ]);*/
        return $this->json($this->usrProvider->loginUser($request)->toApi());
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
}