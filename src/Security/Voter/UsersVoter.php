<?php

namespace App\Security\Voter;

use App\Entity\Users;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UsersVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['view']) && $subject instanceof Users;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        // Si la page ne correspond pas à l'utilisateur connecté, pas d'accès à son contenu
        if ($user === $subject) {
            return true;
        }

        return false;
    }
}
