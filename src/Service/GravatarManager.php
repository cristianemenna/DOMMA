<?php

namespace App\Service;

use App\Repository\UsersRepository;
use Gravatar\Gravatar;

class GravatarManager
{
    public function getAvatar($security)
    {
        $gravatar = new Gravatar();
        $user = $security->getUser();
        $userMail = $user->getEmail();
        $avatar = $gravatar->avatar($userMail, ['d' => 'https://i.ibb.co/r5ZXsZj/avatar-user.png'], false, true);

        return $avatar;
    }

}