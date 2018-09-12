<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 10.09.2018
 * Time: 23:09
 */

namespace App\Tests\User;

use App\Tests\AbstractApiTestCase;
use App\Tests\ApiResponse;
use Symfony\Component\HttpFoundation\Response;
use Faker\Factory;

class AccountTest extends AbstractApiTestCase
{
    public function testUserInfoAction()
    {
        // Login user
        $this->logInAsUser();
        // Make "user info" request
        $response = $this->request('user_info');
        // Check is response code equals 200
        $this->assertEquals(Response::HTTP_OK, $response->getStatus(),
            sprintf('Testing "user_info" request failed, code must be equals %s, but it is not. It is: %s. The content is: %s',
                Response::HTTP_OK, $response->getStatus(), $response->getContent()));
        // Check is response has all necessary params
        $this->assertArrayHasKey('email', $response->getData(),
            sprintf('Testing "user_info" request failed, response must contains "email" param but it`s not. The content is: %s', $response->getContent()));
        $this->assertArrayHasKey('firstName', $response->getData(),
            sprintf('Testing "user_info" request failed, response must contains "firstName" param but it`s not. The content is: %s', $response->getContent()));
        $this->assertArrayHasKey('lastName', $response->getData(),
            sprintf('Testing "user_info" request failed, response must contains "lastName" param but it`s not. The content is: %s', $response->getContent()));
    }

    public function testUpdateAccountAction()
    {
        // Get user
        $this->logInAsUser();
        $user = $this->user;

        $faker = Factory::create();
        // Remember old params
        $oldEmail = $user->getEmail();
        $oldFirstName = $user->getFirstName();
        $oldLastName = $user->getLastName();
        $oldPassword = $user->getPassword();
        // Create new params
        $newEmail = $faker->email;
        $newFirstName = $faker->firstName;
        $newLastName = $faker->lastName;
        $newPassword = $faker->password;

        // 1. Try to update user first and last names
        $data = $this->createUserDataArray(null, $newFirstName, $newLastName);
        // Make updating request
        $response = $this->request('user_update', $data, 'PUT');
        // Check is response code equals 200
        $this->assertEquals(Response::HTTP_OK, $response->getStatus(),
            sprintf('Testing "update user first and last names" request failed, code must be equals %s, but it is not. It is: %s. The content is: %s',
                Response::HTTP_OK, $response->getStatus(), $response->getContent()));
        // Get this user again
        $user = $this->findUser(self::STANDARD_USER);
        // Check first and last names
        $this->assertEquals($newFirstName, $user->getFirstName(),
            sprintf('Testing "update user first and last names" request failed, new first name (%s) is not equals to user current first name (%s)', $newFirstName, $user->getFirstName()));
        $this->assertEquals($newLastName, $user->getLastName(),
            sprintf('Testing "update user first and last names" request failed, new last name (%s) is not equals to user current last name (%s)', $newLastName, $user->getLastName()));

        // 2. Try to update user password
        $data = $this->createUserDataArray(null, null, null, [$newPassword, $newPassword]);
        // Make updating request
        $response = $this->request('user_update', $data, 'PUT');
        // Check is response code equals 200
        $this->assertEquals(Response::HTTP_OK, $response->getStatus(),
            sprintf('Testing "update user password" request failed, code must be equals %s, but it is not. It is: %s. The content is: %s',
                Response::HTTP_OK, $response->getStatus(), $response->getContent()));
        // Get this user again
        $user = $this->findUser(self::STANDARD_USER, true);
        // Check first and last names
        $this->assertNotEquals($oldPassword, $user->getPassword(),
            sprintf('Testing "update user password" request failed, old password (%s) is equals to user current password (%s)', $oldPassword, $user->getPassword()));

        // 3. Try to update user email
        $data = $this->createUserDataArray($newEmail);
        // Make updating request
        $response = $this->request('user_update', $data, 'PUT');
        // Check is response code equals 200
        $this->assertEquals(Response::HTTP_OK, $response->getStatus(),
            sprintf('Testing "update user email" request failed, code must be equals %s, but it is not. It is: %s. The content is: %s',
                Response::HTTP_OK, $response->getStatus(), $response->getContent()));
        // Get this user again
        $user = $this->findUser($newEmail, true);
        // Check email
        $this->assertNotEquals($oldEmail, $user->getEmail(),
            sprintf('Testing "update user email" request failed, old email (%s) is equals to user current email (%s)', $oldEmail, $user->getEmail()));

        // 4. Put all user old params back
        $data = $this->createUserDataArray($oldEmail, $oldFirstName, $oldLastName, [self::DEFAULT_PASSWORD, self::DEFAULT_PASSWORD]);
        // Make updating request
        $response = $this->request('user_update', $data, 'PUT');
        // Check is response code equals 200
        $this->assertEquals(Response::HTTP_OK, $response->getStatus(),
            sprintf('Testing "set user old values back" request failed, code must be equals %s, but it is not. It is: %s. The content is: %s',
                Response::HTTP_OK, $response->getStatus(), $response->getContent()));
        // Get this user again
        $user = $this->findUser($oldEmail, true);
        // Check email
        $this->assertEquals($oldEmail, $user->getEmail(),
            sprintf('Testing "set user old values back" request failed, old email (%s) is not equals to user current email (%s)', $oldEmail, $user->getEmail()));

        // 5. Check updating with invalid email
        $params = $this->createUserDataArray('some_incorrect_email');
        $response = $this->request('user_update', $params, 'PUT');
        $this->checkIsCorrectUserParamsInUpdatingRequestResponse($response, 'Updating user with invalid email', 'not a valid email');

        // 7. Check updating with not much passwords
        $params = $this->createUserDataArray(null, null, null, ['098_password1', '098_password2']);
        $response = $this->request('user_update', $params, 'PUT');
        $this->checkIsCorrectUserParamsInUpdatingRequestResponse($response, 'Updating user with not much passwords', 'are not match');
    }

    private function createUserDataArray(string $email = null, string $firstName = null, string $lastName = null, array $passwords = []): array
    {
        $params = [];

        if ($email !== null) {
            $params['email'] = $email;
        }
        if ($firstName !== null) {
            $params['firstName'] = $firstName;
        }
        if ($lastName !== null) {
            $params['lastName'] = $lastName;
        }
        if (!empty($passwords)) {
            $params['plainPassword'] = ['first' => $passwords[0], 'second' => $passwords[1]];
        }

        return ['user_form' => $params];
    }

    private function checkIsCorrectUserParamsInUpdatingRequestResponse(ApiResponse $response, string $testKeysID, string $errorMessagePart)
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
}