<?php

namespace App\Security\Voter;

use App\Entity\Macro;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class MacroVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['edit']) && $subject instanceof Macro;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        // Si la macro n'appartient pas à l'utilisateur, pas d'accès à son contenu
        if ($user->getMacros()->contains($subject)) {
            return true;
        }

        return false;
    }
}
