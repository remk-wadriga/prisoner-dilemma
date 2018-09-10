<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 07.09.2018
 * Time: 03:00
 */

namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use App\Helpers\AccessTokenEntityInterface;

class AccessToken implements TokenInterface
{
    /**
     * @var AccessTokenEntityInterface
     */
    private $_user;

    /**
     * @var null|array
     */
    private $_credentials;

    private $_attributes = [];

    private $dateTimeFormat = 'Y-m-d H:i:s';

    public function __toString()
    {
        return $this->serialize();
    }

    public function getRoles()
    {
        return [];
    }

    public function getCredentials()
    {
        if ($this->_credentials !== null) {
            return $this->_credentials;
        }
        if ($this->_user === null) {
            return $this->_credentials = [];
        }
        return $this->_credentials = [
            'access_token' => $this->_user->getAccessToken(),
            'renew_token' => $this->_user->getRenewToken(),
            'expired_at' => $this->_user->getAccessTokenExpiredAt(),
        ];
    }

    public function toApi()
    {
        $credentials = $this->getCredentials();
        if (isset($credentials['access_token'])) {
            $credentials['access_token'] = base64_encode($credentials['access_token']);
        }
        if (isset($credentials['renew_token'])) {
            $credentials['renew_token'] = base64_encode($credentials['renew_token']);
        }
        if (isset($credentials['expired_at']) && $credentials['expired_at'] instanceof \DateTimeInterface) {
            $credentials['expired_at'] = $credentials['expired_at']->format($this->dateTimeFormat);
        }
        return $credentials;
    }

    /**
     * @return AccessTokenEntityInterface|null
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * @param AccessTokenEntityInterface $user
     */
    public function setUser($user)
    {
        if (!($user instanceof AccessTokenEntityInterface)) {
            throw new AuthenticationException(sprintf('User must implement % interface to be able get access token', AccessTokenEntityInterface::class));
        }
        $this->_user = $user;
    }

    /**
     * @return null|string
     */
    public function getUsername()
    {
        return $this->_user !== null ? $this->_user->getUsername() : null;
    }

    public function isAuthenticated()
    {
        return !empty($this->getCredentials());
    }

    public function setAuthenticated($isAuthenticated)
    {
        // TODO: Implement setAuthenticated() method.
    }

    public function eraseCredentials()
    {
        $this->_credentials = null;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }

    public function setAttributes(array $attributes)
    {
        $this->_attributes = array_merge($this->_attributes, $attributes);
    }

    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->_attributes);
    }

    public function getAttribute($name)
    {
        return isset($this->_attributes[$name]) ? $this->_attributes[$name] : null;
    }

    public function setAttribute($name, $value)
    {
        $this->_attributes[$name] = $value;
    }

    public function serialize()
    {
        return serialize($this->getCredentials());
    }

    public function unserialize($serialized)
    {
        $this->_credentials = unserialize($serialized);
    }

    public function setDateTimeFormat(string $format)
    {
        $this->dateTimeFormat = $format;
    }
}