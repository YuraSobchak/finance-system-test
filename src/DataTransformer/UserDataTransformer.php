<?php
declare(strict_types=1);

namespace App\DataTransformer;

use App\Entity\User;

/**
 * Class UserDataTransformer
 * @package App\DataTransformer
 */
final class UserDataTransformer
{
    /**
     * @param User $user
     * @return array
     */
    public static function userToArray(User $user): array
    {
        return [
            'username' => $user->getUsername(),
            'email' => $user->getSecureEmail(),
            'amount' => $user->getAmount()
        ];
    }
}
