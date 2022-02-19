<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Exception;

/**
 * Class TransactionService
 * @package App\Service
 */
final class TransactionService
{
    /**
     * @param User $receiver
     * @param float $amount
     * @param User|null $sender
     * @return void
     * @throws Exception
     */
    public function payment(User $receiver, float $amount, ?User $sender = null): void
    {
        // check sender balance for money
        if ($sender && $sender->getAmount() < $amount) {
            throw new Exception("You haven't enough money");
        }

        // updating balance for receiver
        $receiverBalance = $receiver->getAmount();
        $receiver->setAmount($receiverBalance + $amount);

        // updating balance for receiver
        if ($sender) {
            $senderBalance = $sender->getAmount();
            $sender->setAmount($senderBalance - $amount);
        }
    }
}
