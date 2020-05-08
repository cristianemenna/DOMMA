<?php

namespace App\Service;

class PasswordManager
{
    /**
     * Retourne un mot de passe créé de façon aleatoire
     *
     * @return string
     */
    public function randomPassword()
    {
        $password = "";

        $caracteres = "abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789";
        $caractereLength = strlen($caracteres);

            for($i = 1; $i <= 16; $i++)
            {
                $randomPosition = mt_rand(0,($caractereLength - 1));
                $password .= $caracteres[$randomPosition];
            }

        return $password;
    }
}