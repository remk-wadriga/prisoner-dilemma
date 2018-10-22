<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 10.09.2018
 * Time: 17:23
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\UserForm;
use App\Security\AccessTokenAuthenticator;
use App\Security\AccessTokenUserProvider;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends ControllerAbstract
{
    private $authenticator;
    private $userProvider;

    public function __construct(AccessTokenAuthenticator $authenticator, AccessTokenUserProvider $userProvider)
    {
        $this->authenticator = $authenticator;
        $this->userProvider = $userProvider;
    }

    /**
     * @Route("/user", name="user_info", methods={"GET"})
     */
    public function info()
    {
        $user = $this->authenticator->getCurrentUser();
        return $this->json($this->userInfo($user));
    }

    /**
     * @Route("/user", name="user_update", methods={"PUT"})
     */
    public function update(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        // Get current user and set new params
        $user = $this->authenticator->getCurrentUser();
        $form = $this->createJsonForm(UserForm::class, $user, ['action' => UserForm::ACTION_UPDATE]);
        $this->handleJsonForm($form, $request);
        if (!empty($user->getPlainPassword())) {
            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPlainPassword()));
        }
        // Save user entity
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->json($this->userInfo($user));
    }

    private function userInfo(User $user): array
    {
        return [
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
        ];
    }
}