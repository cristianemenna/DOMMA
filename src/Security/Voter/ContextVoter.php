<?php

namespace App\Security\Voter;

use App\Entity\Context;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ContextVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['view']) && $subject instanceof Context;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        // Si le contexte n'appartient pas à l'utilisateur, pas d'accès à son contenu
        if ($user->getContexts()->contains($subject)) {
            return true;
        }

        return false;
    }
}
