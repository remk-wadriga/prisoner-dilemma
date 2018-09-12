<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\AccessTokenAuthenticator;
use App\Tests\AbstractApiTestCase;
use App\Tests\ApiResponse;
use Symfony\Component\HttpFoundation\Response;
use Faker\Factory;
use App\Security\AccessTokenAuthenticationException;

class Oauth2Test extends AbstractApiTestCase
{
    public function testLoginAction()
    {
        // Enable test mode
        $this->isTestMode = true;

        // Check is login action works well with correct users
        // Try to login as user
        $response = $this->logInAsUser();
        // Check status
        $this->assertEquals(200, $response->getStatus(),
            sprintf('Can`t login as user, status code is not 200, it is %s, and content is: %s', $response->getStatus(), $response->getContent()));
        // Check token in response
        $this->checkIsResponseHasCorrectToken($response, 'login as user');

        // Try to login as owner
        $response = $this->logInAsOwner();
        // Check status
        $this->assertEquals(200, $response->getStatus(),
            sprintf('Can`t login as owner, status code is not 200, it is %s, and content is: %s', $response->getStatus(), $response->getContent()));
        // Check token in response
        $this->checkIsResponseHasCorrectToken($response, 'login as owner');

        // Try to login as admin
        $response = $this->logInAsAdmin();
        // Check status
        $this->assertEquals(200, $response->getStatus(),
            sprintf('Can`t login as admin, status code is not 200, it is %s, and content is: %s', $response->getStatus(), $response->getContent()));
        // Check token in response
        $this->checkIsResponseHasCorrectToken($response, 'login as admin');

        // Check is login action works well with incorrect username
        $response = $this->logInAsUser('some invalid usetname', null, 'Login with incorrect username');
        $this->checkIncorrectUserParamsLoginRequestResponse($response, 'Login with incorrect username');

        // Check is login action works well with correct but not existing username
        $response = $this->logInAsUser('some_not_existing_user@gmail.com', null, 'Login with not existing username');
        $this->checkIncorrectUserParamsLoginRequestResponse($response, 'Login with not existing username');

        // Check is login action works well with incorrect password
        $response = $this->logInAsUser(null, 'some_incorrect_password', 'Login with incorrect password');
        $this->checkIncorrectUserParamsLoginRequestResponse($response, 'Login with incorrect password');

        // Everything works well, disable test mode
        $this->clearUserInfo();
        $this->isTestMode = false;
    }

    public function testLogoutAction()
    {
        // 1. Logout user
        // Get user
        $this->logInAsUser();
        $user = $this->user;
        // Get old tokens
        $oldToken = $user->getAccessToken();
        $oldRenewToken = $user->getRenewToken();
        // Send logout request
        $response = $this->request('security_logout', [], 'POST');
        // Check is response code equals 200
        $this->assertEquals(Response::HTTP_OK, $response->getStatus(),
            sprintf('Testing "logout user" request failed, code must be equals %s, but it is not. It is: %s. The content is: %s',
                Response::HTTP_OK, $response->getStatus(), $response->getContent()));
        // Check response body (must contains "OK" string)
        $this->assertContains('OK', $response->getContent(),
            sprintf('Testing "logout user" request failed, response body must contains "OK" string, but it`s not. The content is: %s', $response->getContent()));
        // Check new user tokens (must be not equals to old tokens)
        $user = $this->findUser(self::STANDARD_USER);
        $this->assertNotEquals($oldToken, $user->getAccessToken(),
            sprintf('Testing "logout user" request failed, old (%s) and new (%s) access tokens is the same', $oldToken, $user->getAccessToken()));
        $this->assertNotEquals($oldRenewToken, $user->getRenewToken(),
            sprintf('Testing "logout user" request failed, old (%s) and new (%s) renew tokens is the same', $oldRenewToken, $user->getRenewToken()));

        // 2. Try to get home page with old access token
        $headers = [AccessTokenAuthenticator::ACCESS_TOKEN_HEADER_PARAM_NAME => base64_encode($oldToken)];
        $response = $this->request('app_homepage', [], 'GET', $headers);
        // Check is response code equals 401 (Unauthorized)
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatus(),
            sprintf('Testing "get home page with invalid access token" request failed, code must be equals %s, but it is not. It is: %s. The content is: %s',
                Response::HTTP_UNAUTHORIZED, $response->getStatus(), $response->getContent()));
        // Check response data
        $data = $response->getData();
        $this->assertArrayHasKey('error', $data,
            sprintf('Wrong test "get home page with invalid access token" response format, must contains the "error" param, but it is not. It is: %s', $response->getContent()));
        $this->assertArrayHasKey('message', $data['error'],
            sprintf('Wrong test "get home page with invalid access token" response format, error must contains the "message" param, but it is not. It is: %s', json_encode($data['error'])));
        $this->assertArrayHasKey('code', $data['error'],
            sprintf('Wrong test "get home page with invalid access token" response format, error must contains the "code" param, but it is not. It is: %s', json_encode($data['error'])));
        $this->assertContains('Invalid access token', $data['error']['message'],
            sprintf('Wrong test "get home page with invalid access token" response format, error message must contains the "Invalid access token" word, but it is not. It is: %s', $data['error']['message']));
        $this->assertEquals(AccessTokenAuthenticationException::CODE_INVALID_ACCESS_TOKEN, $data['error']['code'],
            sprintf('Wrong test "get home page with invalid access token" response format, error code must be equals %s, but it is not. It is: %s', AccessTokenAuthenticationException::CODE_INVALID_ACCESS_TOKEN, $data['error']['code']));

        // 3. Try to get the same page with new access token
        $headers = [AccessTokenAuthenticator::ACCESS_TOKEN_HEADER_PARAM_NAME => base64_encode($user->getAccessToken())];
        $response = $this->request('app_homepage', [], 'GET', $headers);
        // Check is response code equals 200
        $this->assertEquals(Response::HTTP_OK, $response->getStatus(),
            sprintf('Testing "get home page with correct access token" request failed, code must be equals %s, but it is not. It is: %s. The content is: %s',
                Response::HTTP_OK, $response->getStatus(), $response->getContent()));
    }

    public function testRegistrationAction()
    {
        // Check correct user registration
        $params = $this->createUserParams();
        $response = $this->request('security_registration', $params, 'POST');
        // Check response status code (must be equals to 200)
        $this->assertEquals(Response::HTTP_OK, $response->getStatus(),
            sprintf('Testing correct user registration request failed, code must be equals %s, but it is not. It is: %s. The content is: %s',
                Response::HTTP_OK, $response->getStatus(), $response->getContent()));
        // Check all access token params
        $this->checkIsResponseHasCorrectToken($response, 'Registration action');

        // Let`s find this user by access token, check is it exists and then remove them
        $accessToken = $response->get('access_token');
        $repository = $this->entityManager->getRepository(User::class);
        $user = $repository->findOneBy(['access_token' => base64_decode($accessToken)]);
        $this->assertNotNull($user, sprintf('Can`t fund user by token %s', $accessToken));
        // 3. Delete just registered user user
        $errorMessage = '';
        try {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            $result = true;
        } catch (\Exception $e) {
            $result = false;
            $errorMessage = $e->getMessage();
        }
        $this->assertTrue($result === true,
            sprintf('Testing valid user registration error: %s', $errorMessage));


        // Check registration with existing email
        // 1. Get user
        $user = $this->findUser(self::STANDARD_USER);
        // 2. Create new user params with the same email
        $params = $this->createUserParams($user->getEmail());
        // 3. Send request
        $response = $this->request('security_registration', $params, 'POST');
        // Check response
        $this->checkIncorrectUserParamsRegistrationRequestResponse($response, 'Registration user with existing email', 'already registered');

        // Check registration with invalid email
        $params = $this->createUserParams('some_incorrect_email');
        $response = $this->request('security_registration', $params, 'POST');
        $this->checkIncorrectUserParamsRegistrationRequestResponse($response, 'Registration user with invalid email', 'not a valid email');

        // Check registration without passwords
        $params = $this->createUserParams(null, [null, null]);
        $response = $this->request('security_registration', $params, 'POST');
        $this->checkIncorrectUserParamsRegistrationRequestResponse($response, 'Registration user without passwords', 'not be blank');

        // Check registration with not much passwords
        $params = $this->createUserParams(null, ['098_password1', '098_password2']);
        $response = $this->request('security_registration', $params, 'POST');
        $this->checkIncorrectUserParamsRegistrationRequestResponse($response, 'Registration user with not much passwords', 'are not match');
    }

    public function testRenewTokenAction()
    {
        // 1. Get user
        $user = $this->findUser(self::STANDARD_USER);
        $this->assertNotNull($user, sprintf('Where is my standard user (%s)?!', self::STANDARD_USER));
        // 2. Remember this user actual access token
        $this->accessToken = base64_encode($user->getAccessToken());

        // 3. Set for this user old "access_token_expired_at" date
        $user->setAccessTokenExpiredAt(Factory::create()->dateTimeBetween('-1 month', 'now'));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // 4. Make request to homepage
        $response = $this->request('app_homepage');
        // 5. Check response
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatus(),
            sprintf('Wrong test "make request with expired token" response format, status code mus be equal to %s, but it is not. It is: %s. The content is: %s',
                Response::HTTP_UNAUTHORIZED, $response->getStatus(), $response->getContent()));
        $data = $response->getData();$this->assertArrayHasKey('error', $data,
        sprintf('Wrong test "make request with expired token" response format, must contains the "error" param, but it is not. It is: %s', $response->getContent()));
        $this->assertArrayHasKey('message', $data['error'],
            sprintf('Wrong test "make request with expired token" response format, error must contains the "message" param, but it is not. It is: %s', json_encode($data['error'])));
        $this->assertArrayHasKey('code', $data['error'],
            sprintf('Wrong test "make request with expired token" response format, error must contains the "code" param, but it is not. It is: %s', json_encode($data['error'])));
        $this->assertContains('expired', $data['error']['message'],
            sprintf('Wrong test "make request with expired token" response format, error message must contains the "expired" word, but it is not. It is: %s', $data['error']['message']));
        $this->assertEquals(AccessTokenAuthenticationException::CODE_ACCESS_TOKEN_EXPIRED, $data['error']['code'],
            sprintf('Wrong test "make request with expired token" response format, error code must be equals %s, but it is not. It is: %s',
                AccessTokenAuthenticationException::CODE_ACCESS_TOKEN_EXPIRED, $data['error']['code']));

        // 6. Try to renew user access token
        $response = $this->request('security_renew_token', ['renew_token' => base64_encode($user->getRenewToken())], 'POST');
        $this->assertEquals(Response::HTTP_OK, $response->getStatus(),
            sprintf('Wrong test "make request with expired token" response format, status code mus be equal to %s, but it is not. It is: %s. The content is: %s',
                Response::HTTP_OK, $response->getStatus(), $response->getContent()));
        $this->checkIsResponseHasCorrectToken($response, 'Renew token action');
    }


    private function checkIncorrectUserParamsLoginRequestResponse(ApiResponse $response, string $testKeysID)
    {
        $data = $response->getData();
        $this->assertArrayHasKey('error', $data,
            sprintf('Wrong test "%s" response format, must contains the "error" param, but it is not. It is: %s', $testKeysID, $response->getContent()));
        $this->assertArrayHasKey('message', $data['error'],
            sprintf('Wrong test "%s" response format, error must contains the "message" param, but it is not. It is: %s', $testKeysID, json_encode($data['error'])));
        $this->assertArrayHasKey('code', $data['error'],
            sprintf('Wrong test "%s" response format, error must contains the "code" param, but it is not. It is: %s', $testKeysID, json_encode($data['error'])));
        $this->assertContains('incorrect', $data['error']['message'],
            sprintf('Wrong test "%s" response format, error message must contains the "incorrect" word, but it is not. It is: %s', $testKeysID, $data['error']['message']));
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $data['error']['code'],
            sprintf('Wrong test "%s" response format, error code must be equals %s, but it is not. It is: %s', $testKeysID, Response::HTTP_UNAUTHORIZED, $data['error']['code']));
    }

    private function checkIncorrectUserParamsRegistrationRequestResponse(ApiResponse $response, string $testKeysID, string $errorMessagePart)
    {
        // Check response status - mus be equals to 400
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatus(),
            sprintf('Wrong test "%s" response format, status code mus be equal to %s, but it is not. It is: %s. The content is: %s',
                $testKeysID, Response::HTTP_BAD_REQUEST, $response->getStatus(), $response->getContent()));
        // Check response data
        $data = $response->getData();
        $this->assertArrayHasKey('error', $data,
            sprintf('Wrong test "%s" response format, must contains the "error" param, but it is not. It is: %s', $testKeysID, $response->getContent()));
        $this->assertArrayHasKey('message', $data['error'],
            sprintf('Wrong test "%s" response format, error must contains the "message" param, but it is not. It is: %s', $testKeysID, json_encode($data['error'])));
        $this->assertArrayHasKey('code', $data['error'],
            sprintf('Wrong test "%s" response format, error must contains the "code" param, but it is not. It is: %s', $testKeysID, json_encode($data['error'])));
        $this->assertContains($errorMessagePart, $data['error']['message'],
            sprintf('Wrong test "%s" response format, error message must contains the "%s" word, but it is not. It is: %s', $testKeysID, $errorMessagePart, $data['error']['message']));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $data['error']['code'],
            sprintf('Wrong test "%s" response format, error code must be equals %s, but it is not. It is: %s', $testKeysID, Response::HTTP_BAD_REQUEST, $data['error']['code']));
    }

    private function createUserParams(string $email = null, array $passwords = [], string $firstName = null, string $lastName = null): array
    {
        $faker = Factory::create();
        if ($email === null) {
            $email = $faker->email;
        }
        if (empty($passwords)) {
            $password = $faker->password;
            $passwords = [$password, $password];
        }
        list($passwords['first'], $passwords['second']) = $passwords;
        unset($passwords[0], $passwords[1]);
        if ($firstName === null) {
            $firstName = $faker->firstName;
        }
        if ($lastName === null) {
            $lastName = $faker->lastName;
        }

        return [
            'user_form' => [
                'email' => $email,
                'plainPassword' => $passwords,
                'firstName' => $firstName,
                'lastName' => $lastName,
            ],
        ];
    }
}
