<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\Transaction;
use App\Entity\User;

/**
 * Class TransactionFactory
 * @package App\Factory
 */
final class TransactionFactory
{
    public function create(User $receiver, float $amount, ?User $sender = null): Transaction
    {
        $transaction = new Transaction();
        $transaction->setReceiver($receiver);
        $transaction->setAmount($amount);
        $transaction->setSender($sender);
        return $transaction;
    }
}