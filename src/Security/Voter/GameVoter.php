<?php

namespace App\Security\Voter;

use App\Entity\Game;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class GameVoter extends Voter
{
    const ACTION_MANAGE = 'MANAGE';

    private $security;

    protected static function getAllowedActions(): array
    {
        return [
            self::ACTION_MANAGE,
        ];
    }

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, self::getAllowedActions()) && $subject instanceof Game;
    }

    /**
     * @param string $attribute
     * @param Game $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::ACTION_MANAGE:
                return $this->security->isGranted('ROLE_ADMIN', $user) || $subject->getUser() == $user;
                break;
        }

        return false;
    }
}
