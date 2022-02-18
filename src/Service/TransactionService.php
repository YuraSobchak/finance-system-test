<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

/**
 * Class TransactionService
 * @package App\Service
 */
final class TransactionService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $receiver
     * @param float $amount
     * @param User|null $sender
     * @return Transaction
     */
    public function payment(string $receiver, float $amount, ?User $sender = null): Transaction
    {
        // check sender balance for money
        if ($sender && $sender->getAmount() < $amount) {
            throw new Exception("You haven't enough money");
        }

        // new Transaction
        $transaction = new Transaction();
        $receiver = $this->em->getRepository(User::class)->findByUsernameOrEmail($receiver);
        $transaction->setReceiver($receiver);
        if ($sender) {
            $transaction->setSender($sender);
        }
        $transaction->setAmount($amount);
        $this->em->persist($transaction);

        // updating users balances
        $this->calculateUsersBalance($receiver, $amount, $sender);
        $this->em->flush();

        return $transaction;
    }

    public function calculateUsersBalance(User $receiver, float $amount, ?User $sender)
    {
        // updating balance for receiver
        $receiverBalance = $receiver->getAmount();
        $receiver->setAmount($receiverBalance + $amount);
        $this->em->persist($receiver);

        // updating balance for receiver
        if ($sender) {
            $senderBalance = $sender->getAmount();
            $sender->setAmount($senderBalance - $amount);
            $this->em->persist($sender);
        }
    }
}
