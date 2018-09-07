<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 07.09.2018
 * Time: 01:58
 */

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use App\Helpers\AccessTokenEntityInterface;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\ORMException;
use App\Helpers\AccessTokenHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AccessTokenUserProvider implements UserProviderInterface
{
    private $doctrine;
    private $encoder;
    private $container;

    public function __construct(RegistryInterface $registry, UserPasswordEncoderInterface $encoder, ContainerInterface $container)
    {
        $this->doctrine = $registry;
        $this->encoder = $encoder;
        $this->container = $container;
    }

    /**
     * Login user: try to find it by username and check the password
     * Throws "UNAUTHORIZED" (401) HTTP exception
     *
     * @param Request $request
     * @return \App\Security\AccessToken
     */
    public function loginUser(Request $request): AccessToken
    {
        // 1. Check is request has required params
        $username = $request->get('username');
        $password = $request->get('password');
        if (empty($username) || empty($password)) {
           throw new AuthenticationException('Params "username" and "password" area required!', Response::HTTP_UNAUTHORIZED);
        }
        // 2. Try to find user by username and if it`s found check the password
        $user = $this->getUserByUsername($username);
        if ($user === null || $this->encoder->isPasswordValid($user, $password) !== true) {
            throw new AuthenticationException('Username or password incorrect', Response::HTTP_UNAUTHORIZED);
        }
        // 3. Create and return user access token
        return $this->createAccessToken($user);
    }

    /**
     * Search user in DB by it`s username
     *
     * @param string $username
     * @return null|AccessTokenEntityInterface
     */
    public function getUserByUsername(string $username): ?AccessTokenEntityInterface
    {
        /** @var UserRepository $repository */
        $repository = $this->doctrine->getRepository(User::class);
        return $repository->findOneBy(['email' => $username]);
    }

    public function getUserByAccessToken(string $accessToken): ?AccessTokenEntityInterface
    {
        /** @var UserRepository $repository */
        $repository = $this->doctrine->getRepository(User::class);
        return $repository->findOneBy(['access_token' => $accessToken]);
    }

    /**
     * Create an access token object with successfully authenticated user credentials
     * This method should run only if user is authenticated correctly!
     *
     * @param AccessTokenEntityInterface $user
     * @return \App\Security\AccessToken
     */
    public function createAccessToken(AccessTokenEntityInterface $user): AccessToken
    {
        $manager = $this->doctrine->getEntityManager();
        try {
            $manager->persist($user);
            $user
                ->setAccessToken(AccessTokenHelper::generateAccessToken($user))
                ->setRenewToken(AccessTokenHelper::generateAccessToken($user))
                ->setAccessTokenExpiredAt(AccessTokenHelper::getAccessTokenExpiredAt());
            $manager->flush();
        } catch (ORMException $e) {
            throw new AuthenticationException(sprintf('Can`t create token for user: %s', $e->getMessage()), null, $e);
        }

        return $this->getAccessTokenForUser($user);
    }

    /**
     * Just create new AccessToken instance for successfully authenticated user
     *
     * @param AccessTokenEntityInterface $user
     * @return AccessToken
     */
    public function getAccessTokenForUser(AccessTokenEntityInterface $user): AccessToken
    {
        $token = new AccessToken();
        $token->setUser($user);
        if ($this->container->hasParameter('frontend_date_time_format')) {
            $token->setDateTimeFormat($this->container->getParameter('frontend_date_time_format'));
        }
        return $token;
    }

    public function loadUserByUsername($username)
    {
        return $this->getUserByUsername($username);
    }

    public function refreshUser(UserInterface $user)
    {
        return $user;
    }

    public function supportsClass($class)
    {
        // TODO: Implement supportsClass() method.
    }


}