<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 06.09.2018
 * Time: 15:19
 */

namespace App\Security;


use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use App\Helpers\AccessTokenEntityInterface;

class AccessTokenAuthenticator extends AbstractGuardAuthenticator
{
    private $router;
    /**
     * @var AccessTokenUserProvider
     */
    private $provider;

    /**
     * @var AccessTokenEntityInterface|null
     */
    private $user;

    /**
     * @var AccessToken|null
     */
    private $accessToken;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser(). Returning null will cause this authenticator
     * to be skipped.
    */
    public function getCredentials(Request $request)
    {
        // 1. Try to get token from headers:
        $token = $request->headers->get('X-AUTH-TOKEN');
        if (empty($token)) {
            $token = $request->query->get('access_token');
        }
        if (empty($token)) {
            // No token?
            $token = null;
        }
        // What you return here will be passed to getUser() as $credentials
        $credentials = [
            'access_token' => base64_decode($token),
        ];
        if ($request->get('renew_token') !== null) {
            $credentials['renew_token'] = base64_decode($request->get('renew_token'));
        }
        return $credentials;
    }

    /**
     * After success full authentication ycu can gat an user access token
     *
     * @return AccessToken
     */
    public function getAccessToken(): AccessToken
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }
        if ($this->user === null) {
            throw new AuthenticationException('User is not authorized yet, so it`s has no access token', AccessTokenAuthenticationException::CODE_SYSTEM_ERROR);
        }
        if ($this->provider === null) {
            throw new AuthenticationException('User provider is not exists yet, so it`s no way to get an access token', AccessTokenAuthenticationException::CODE_SYSTEM_ERROR);
        }
        return $this->provider->getAccessTokenForUser($this->user);
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return AccessTokenEntityInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if ($this->user !== null) {
            return $this->user;
        }
        $this->provider = $userProvider;
        if (!($userProvider instanceof AccessTokenUserProvider)) {
            throw new AuthenticationException(
                sprintf('User provider must instance of %s, %s given', AccessTokenUserProvider::class, get_class($userProvider)),
                AccessTokenAuthenticationException::CODE_SYSTEM_ERROR);
        }
        $accessToken = isset($credentials['access_token']) ? $credentials['access_token'] : null;
        if ($accessToken === null) {
            return null;
        }
        // if null, authentication will fail
        // if a User object, checkCredentials() is called
        $this->user = $userProvider->getUserByAccessToken($accessToken);
        if ($this->user === null) {
            throw new AuthenticationException('Invalid access token', AccessTokenAuthenticationException::CODE_INVALID_ACCESS_TOKEN);
        }
        return $this->user;
    }

    /**
     * @param mixed $credentials
     * @param AccessTokenEntityInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        if (!($user instanceof AccessTokenEntityInterface)) {
            throw new AuthenticationException(
                sprintf('User entity must instance of %s, %s given', AccessTokenEntityInterface::class, get_class($user)),
                AccessTokenAuthenticationException::CODE_SYSTEM_ERROR);
        }
        //print_r([$user->getAccessTokenExpiredAt()->getTimestamp(), (new \DateTime())->getTimestamp()]); echo "\n"; exit();
        // If access token is not expired - just return the true, because it`s all right
        if ($user->getAccessTokenExpiredAt()->getTimestamp() > (new \DateTime())->getTimestamp()) {
            return true;
        }
        // Else - check is request has "renew_token" params and it`t equals current user renew_token
        if (isset($credentials['renew_token']) && $credentials['renew_token'] === $user->getRenewToken()) {
            // Create new access token for user and return true
            if ($this->provider === null) {
                throw new AuthenticationException('User entity provider is missing', AccessTokenAuthenticationException::CODE_SYSTEM_ERROR);
            }
            $this->provider->createAccessToken($user);
            return true;
        } else {
            // Else - throw "access token expired exception"
            throw new AuthenticationException('Access token expired', AccessTokenAuthenticationException::CODE_ACCESS_TOKEN_EXPIRED);
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            // you might translate this message
            'message' => $authException->getMessage(),
        ];
        return new JsonResponse($data, $authException->getCode());
    }

    public function supports(Request $request)
    {
        return $request->getPathInfo() !== $this->router->generate('security_login');
    }

    public function supportsRememberMe()
    {
        return false;
    }

}