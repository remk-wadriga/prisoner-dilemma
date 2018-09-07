<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 06.09.2018
 * Time: 13:54
 */

namespace App\Helpers;
use Symfony\Component\Security\Core\User\UserInterface;

interface AccessTokenEntityInterface extends UserInterface, \Serializable
{
    /**
     * @param string $access_token
     * @return self
     */
    public function setAccessToken(string $access_token);

    public function getAccessToken(): ?string;

    /**
     * @param string $renewToken
     * @return self
     */
    public function setRenewToken(string $renewToken);

    public function getRenewToken(): ?string;

    /**
     * @param \DateTimeInterface $access_token_expired_at
     * @return self
     */
    public function setAccessTokenExpiredAt(\DateTimeInterface $access_token_expired_at);

    public function getAccessTokenExpiredAt(): ?\DateTimeInterface;
}