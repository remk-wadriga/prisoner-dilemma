<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 07.09.2018
 * Time: 14:17
 */

namespace App\Tests;

use App\Entity\User;
use App\Repository\Service\TotalStatisticsRepository;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Security\AccessTokenAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class AbstractApiTestCase extends WebTestCase
{
    const STANDARD_USER = 'user@gmail.com';
    const STANDARD_OWNER = 'owner@gmail.com';
    const STANDARD_ADMIN = 'admin@gmail.com';
    const DEFAULT_PASSWORD = 'test';

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    protected $router;

    /** @var \App\Entity\User|nul */
    protected $user;

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

    protected function findUser($conditions, bool $forgetUser = false): User
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

    protected function request($routeName, array $data = [], string $method = 'GET', array $headers = [], array $files = []): ?ApiResponse
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
        // If thit is json request - convert data to json
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
        if (is_array($routeName)) {
            $prams = $routeName[1];
            $routeName = $routeName[0];
        } else {
            $prams = [];
        }
        $url = $this->router->generate($routeName, $prams);

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
        if (!$this->isTestMode) {
            if ($this->user === null) {
                $this->user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $username]);
                // If user token is steel alive, then save this user token and return null
                if ($this->user !== null && $this->user->getAccessTokenExpiredAt()->getTimestamp() > (new \DateTime())->getTimestamp()) {
                    $this->accessToken = base64_encode($this->user->getAccessToken());
                    $this->renewToken = base64_encode($this->user->getRenewToken());
                    $this->tokenExpiredAt = $this->user->getAccessTokenExpiredAt()->format('Y-m-d H:i:s');
                    return null;
                }
            }
        }

        // Create body params for login request
        $params = [
            'username' => $username,
            'password' => $password,
        ];
        // Send response
        $response = $this->request('security_login', $params, 'POST');

        // It thit is not test mode request - check response
        if (!$this->isTestMode) {
            // Check status
            $this->assertEquals(200, $response->getStatus(),
                sprintf('Can`t login %s, status code is not 200, it is %s, and content is: %s', $loginID, $response->getStatus(), $response->getContent()));
            // Check token in response
            list($this->accessToken, $this->renewToken, $this->tokenExpiredAt) = $this->checkIsResponseHasCorrectToken($response);
            $this->user = $this->entityManager->getRepository(User::class)->findOneBy(['accessToken' => base64_decode($this->accessToken)]);
        }

        // If everything is all right or thit is test mode request, just return the API response
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

    protected function checkResponseParams(ApiResponse $response, string $testKeysID, array $params, $singleResult = false)
    {
        // Check status
        $this->assertEquals(Response::HTTP_OK, $response->getStatus(),
            sprintf('Wrong test "%s" response format, status code must be equal to %s, but it is not. It is: %s. The content is: %s',
                $testKeysID, Response::HTTP_FORBIDDEN, $response->getStatus(), $response->getContent()));
        // Check data
        $data = $response->getData();
        $this->assertInternalType('array', $data, sprintf('Wrong test "%s" response. The response data must be an array, but "%s" given. Data: %s',
            $testKeysID, gettype($data), $response->getContent()));
        if ($singleResult) {
            $data = [$data];
        }
        foreach ($data as $stats) {
            $jsonData = json_encode($stats);
            $this->assertInternalType('array', $stats, sprintf('Wrong test "%s" response. Each response data item must be an array, but "%s" given. Data: %s',
                $testKeysID, gettype($stats), $jsonData));

            foreach ($params as $attr => $type) {
                if (is_array($type)) {
                    $type1 = $type[0];
                    $type2 = $type[1];
                    $type = $type1;
                    if (isset($stats[$attr]) && gettype($stats[$attr]) === $type2) {
                        settype($stats[$attr], $type1);
                    }
                }

                $this->assertArrayHasKey($attr, $stats, sprintf('Wrong test "%s" response. Each response data item have the "%s" param, but it\'s not. Data: %s',
                    $testKeysID, $attr, $jsonData));
                $this->assertInternalType($type, $stats[$attr], sprintf('Wrong test "%s" response. Each response data.%s item must be a %s, but "%s" given. Data: %s',
                    $testKeysID, $attr, $type, gettype($stats[$attr]), $jsonData));
            }
        }
    }

    protected function clearUserInfo()
    {
        $this->accessToken = null;
        $this->renewToken = null;
        $this->tokenExpiredAt = null;
        $this->user = null;
    }

    protected function getParam($name)
    {
        $container = self::$kernel->getContainer();
        if (!$container->hasParameter($name)) {
            return null;
        }
        return str_replace('0/0', '%', $container->getParameter($name));
    }

    protected function getRandomDatesPeriod($daysCount = null)
    {
        /** @var TotalStatisticsRepository $repository */
        $repository = new TotalStatisticsRepository($this->entityManager, static::$container);
        $dates = $repository->getFirstAndLastGamesDates($this->user);
        $faker = Factory::create();

        $dates['toDate'] = (new \DateTime($dates['end']))
            ->modify(sprintf('-%s days', $faker->numberBetween(0, 5)))
            ->format($this->getParam('backend_date_format'));

        if ($daysCount === null) {
            $daysCount = $faker->numberBetween(1, 10);
        }

        $dates['fromDate'] = (new \DateTime($dates['toDate']))
            ->modify(sprintf('-%s days', $daysCount))
            ->format($this->getParam('backend_date_format'));

        unset($dates['start'], $dates['end']);
        return $dates;
    }

    protected function createRandomGameParamsFilters()
    {
        $faker = Factory::create();
        return [
            'game_roundsCount' => $faker->numberBetween(10, 100),
            'game_balesForWin' => $faker->numberBetween(20, 50),
            'game_balesForLoos' => $faker->numberBetween(-20, 0),
            'game_balesForCooperation' => $faker->numberBetween(-10, 10),
            'game_balesForDraw' => $faker->numberBetween(-10, 10),
        ];
    }
}