<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 06.09.2018
 * Time: 15:19
 */

namespace App\Security;


use App\Entity\User;
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
    const ACCESS_TOKEN_HEADER_PARAM_NAME = 'X-AUTH-TOKEN';

    const ACCESS_TOKEN_URI_PARAM_NAME = 'access_token';

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
     * @return User
     */
    public function getCurrentUser(): AccessTokenEntityInterface
    {
        return $this->getAccessToken()->getUser();
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser(). Returning null will cause this authenticator
     * to be skipped.
    */
    public function getCredentials(Request $request)
    {
        // 1. Try to get token from headers:
        $token = $request->headers->get(self::ACCESS_TOKEN_HEADER_PARAM_NAME);
        if (empty($token)) {
            $token = $request->query->get(self::ACCESS_TOKEN_URI_PARAM_NAME);
        }
        if (empty($token)) {
            // No token?
            $token = null;
        }
        // What you return here will be passed to getUser() as $credentials
        $credentials = [
            'access_token' => base64_decode($token),
        ];
        if ($request->getPathInfo() === $this->router->generate('security_renew_token')) {
            try {
                $params = json_decode($request->getContent(), true);
                if ($params === null) {
                    throw new \Exception(sprintf('Request body has invalid json format: %s', $request->getContent()));
                }
            } catch (\Exception $e) {
                throw new AuthenticationException(sprintf('Can`t parse request body: %s', $e->getMessage()), AccessTokenAuthenticationException::CODE_INVALID_REQUEST_PARAMS);
            }
            $renewToken = isset($params['renew_token']) ? $params['renew_token'] : null;
            if ($renewToken === null) {
                throw new AuthenticationException('Param "renew_token" is required', AccessTokenAuthenticationException::CODE_REQUIRED_PARAM_MISSING);
            }
            $credentials['renew_token'] = base64_decode($renewToken);
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
        $accessToken = isset($credentials['access_token']) && !empty($credentials['access_token']) ? $credentials['access_token'] : null;
        if ($accessToken === null) {
            throw new AuthenticationException('Access token missed', AccessTokenAuthenticationException::CODE_REQUIRED_PARAM_MISSING);
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

        // If this is "renew token" request
        if (isset($credentials['renew_token'])) {
            // First of all check the renew token from request
            if ($credentials['renew_token'] !== $user->getRenewToken()) {
                throw new AuthenticationException('Invalid renew token', AccessTokenAuthenticationException::CODE_INVALID_REQUEST_PARAMS);
            }
            // Second (if renew token from request is correct) -
            // Check is user provider isset
            if ($this->provider === null) {
                throw new AuthenticationException('User entity provider is missing', AccessTokenAuthenticationException::CODE_SYSTEM_ERROR);
            }
            // And renew user access token
            $this->accessToken = $this->provider->createAccessToken($user);

            // So, this is all - user has a new access token and already authenticated, just return the true
            return true;
        }

        // This is not "renew token" request, so...
        // If access token is not expired - just return the true, because it`s all right
        if ($user->getAccessTokenExpiredAt()->getTimestamp() > (new \DateTime())->getTimestamp()) {
            return true;
        } else {
            // Else - throw "access token expired" exception
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
        $code = $exception->getCode() !== AccessTokenAuthenticationException::CODE_SYSTEM_ERROR ?
            Response::HTTP_UNAUTHORIZED : Response::HTTP_INTERNAL_SERVER_ERROR;
        return new JsonResponse(['error' => $data], $code);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            'message' => $authException->getMessage(),
            'code' => $authException->getCode(),
        ];
        return new JsonResponse(['error' => $data], Response::HTTP_UNAUTHORIZED);
    }

    public function supports(Request $request)
    {
        return !in_array($request->getPathInfo(), $this->getUnsopportedUrls());
    }

    public function supportsRememberMe()
    {
        return false;
    }

    private function getUnsopportedUrls(): array
    {
        return [
            $this->router->generate('security_login'),
            $this->router->generate('security_registration'),
        ];
    }
}