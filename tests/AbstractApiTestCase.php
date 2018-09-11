<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 07.09.2018
 * Time: 14:17
 */

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Security\AccessTokenAuthenticator;
use Doctrine\ORM\EntityManagerInterface;

class AbstractApiTestCase extends WebTestCase
{
    const STANDARD_USER = 'user@gmail.com';
    const STANDARD_OWNER = 'owner@gmail.com';
    const STANDARD_ADMIN = 'remkwadriga2013@gmail.com';
    const DEFAULT_PASSWORD = 'test';

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;
    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var string
     */
    protected $renewToken;

    /**
     * @var string
     */
    protected $tokenExpiredAt;

    protected $isTestMode = false;

    public function setUp()
    {
        parent::setUp();
        self::bootKernel();
        $this->client = static::createClient();
        $this->router = $this->client->getContainer()->get('router');
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
    }

    protected function findUser($conditions, bool $forgetUser = true): User
    {
        if (!is_array($conditions)) {
            $conditions = ['email' => $conditions];
        }
        $errorMessage = 'User not found in DB';
        /** @var \App\Repository\UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        /** @var \App\Entity\User $lastUser */
        try {
            // Find user by conditions
            $user = $userRepository->findOneBy($conditions);
            // Remove doctrine cache
            if ($forgetUser) {
                $this->entityManager->clear(User::class);
            }
        } catch (\Exception $e) {
            $errorMessage .= ': ' . $e->getMessage();
            $user = null;
        }
        $this->assertNotNull($user, $errorMessage);
        return $user;
    }

    protected function request(string $routeName, array $data = [], string $method = 'GET', array $headers = [], array $files = []): ?ApiResponse
    {
        // Set default content type header
        if (!isset($headers['CONTENT_TYPE'])) {
            $headers['CONTENT_TYPE'] = 'application/json';
        }
        // If user is already logged in, add the access token in request headers
        if ($this->accessToken !== null && !isset($headers[AccessTokenAuthenticator::ACCESS_TOKEN_HEADER_PARAM_NAME])) {
            $headers[AccessTokenAuthenticator::ACCESS_TOKEN_HEADER_PARAM_NAME] = $this->accessToken;
        }
        // Add "HTTP_" prefix for all headers
        foreach ($headers as $name => $header) {
            if (!in_array($name, ['CONTENT_TYPE', 'HTTP_REFERER'])) {
                $headers['HTTP_' . $name] = $header;
                unset($headers[$name]);
            }
        }
        // If this is json request - convert data to json
        if ($headers['CONTENT_TYPE'] === 'application/json') {
            $body = is_array($data) ? json_encode($data) : $data;
            $data = [];
        } else {
            // In other cases request params must be an array
            if (!is_array($data)) {
                $data = [];
            }
            $body = '';
        }

        // Create url by route name
        $url = $this->router->generate($routeName);

        // Make request
        $crawler = $this->client->request($method, $url, $data, $files, $headers, $body);

        // Process response
        try {
            // and return it if everything is OK
            return new ApiResponse($this->client->getResponse(), $crawler);
        } catch (\Exception $e) {
            // if something is wrong - throw standard request test error and return null
            $this->assertTrue(false, sprintf('Request error: %s', $e->getMessage()));
            return null;
        }
    }

    protected function logInAsUser($username = null, $password = null, $loginID = 'user'): ?ApiResponse
    {
        if ($username === null) {
            $username = self::STANDARD_USER;
        }
        if ($password === null) {
            $password = self::DEFAULT_PASSWORD;
        }
        return $this->logIn($username, $password, $loginID);
    }

    protected function logInAsOwner($username = null, $password = null, $loginID = 'owner'): ?ApiResponse
    {
        if ($username === null) {
            $username = self::STANDARD_OWNER;
        }
        if ($password === null) {
            $password = self::DEFAULT_PASSWORD;
        }
        return $this->logIn($username, $password, $loginID);
    }

    protected function logInAsAdmin($username = null, $password = null, $loginID = 'admin'): ?ApiResponse
    {
        if ($username === null) {
            $username = self::STANDARD_ADMIN;
        }
        if ($password === null) {
            $password = self::DEFAULT_PASSWORD;
        }
        return $this->logIn($username, $password, $loginID);
    }

    protected function logIn($username, $password, $loginID = 'user'): ?ApiResponse
    {
        // Create body params for login request
        $params = [
            'username' => $username,
            'password' => $password,
        ];
        // Send response
        $response = $this->request('security_login', $params, 'POST');

        // It this is not test mode request - check response
        if (!$this->isTestMode) {
            // Check status
            $this->assertEquals(200, $response->getStatus(),
                sprintf('Can`t login %s, status code is not 200, is is %s, and content is: %s', $loginID, $response->getStatus(), $response->getContent()));
            // Check token in response
            list($this->accessToken, $this->renewToken, $this->tokenExpiredAt) = $this->checkIsResponseHasCorrectToken($response);
        }

        // If everything is all right or this is test mode request, just return the API response
        return $response;
    }

    protected function checkIsResponseHasCorrectToken(ApiResponse $response, string $testID = 'Login action'): array
    {
        // Get access token params
        $accessToken = $response->get('access_token');
        $renewToken = $response->get('renew_token');
        $tokenExpiredAt = $response->get('expired_at');
        // Check all access token params
        $this->assertNotNull($accessToken, sprintf('Testing "%" failed. The response does not contains "access_token" param, it is: %', $testID, $response->getContent()));
        $this->assertNotNull($renewToken, sprintf('Testing "%" failed. The response does not contains "renew_token" param, it is: %', $testID, $response->getContent()));
        $this->assertNotNull($tokenExpiredAt, sprintf('Testing "%" failed. The response does not contains "expired_at" param, it is: %', $testID, $response->getContent()));

        return [$accessToken, $renewToken, $tokenExpiredAt];
    }
}