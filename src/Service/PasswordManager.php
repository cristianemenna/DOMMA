<?php

namespace App\Service;

use App\Repository\UsersRepository;

class PasswordManager
{
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