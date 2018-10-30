<?php

namespace App\Security\Voter;

use App\Entity\Game;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class GameVoter extends Voter
{
    const ACTION_MANAGE = 'MANAGE';

    protected static function getAllowedActions(): array
    {
        return [
            self::ACTION_MANAGE,
        ];
    }

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, self::getAllowedActions()) && $subject instanceof Game;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case 'EDIT':
                // logic to determine if the user can EDIT
                // return true or false
                break;
            case 'VIEW':
                // logic to determine if the user can VIEW
                // return true or false
                break;
        }

        return false;
    }
}
