<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\User;

/**
 * Class UserService
 * @package App\Service
 */
final class UserService
{
    private SecurityService $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * @param User $user
     * @param string $password
     * @return User
     */
    public function updateUserPassword(User $user, string $password): User
    {
        $password = $this->securityService->hashPassword($user, $password);
        $user->setPassword($password);

        return $user;
    }
}
