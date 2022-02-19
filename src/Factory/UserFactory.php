<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\User;

/**
 * Class UserFactory
 * @package App\Factory
 */
final class UserFactory
{
    public function create(string $email, string $username): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        return $user;
    }
}