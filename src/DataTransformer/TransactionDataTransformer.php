<?php
declare(strict_types=1);

namespace App\DataTransformer;

use App\Entity\Transaction;

/**
 * Class TransactionDataTransformer
 * @package App\DataTransformer
 */
final class TransactionDataTransformer
{
    /**
     * @param $transactions
     * @return array
     */
    public static function transactionsToArray($transactions): array
    {
        $data = [];
        foreach ($transactions as $transaction) {
            $data[] = self::transactionToArray($transaction);
        }

        return $data;
    }

    /**
     * @param Transaction $transaction
     * @return array
     */
    public static function transactionToArray(Transaction $transaction): array
    {
        return [
            'sender' => $transaction->getSender()
                ? UserDataTransformer::userTransactionToArray($transaction->getSender())
                : null,
            'receiver' => UserDataTransformer::userTransactionToArray($transaction->getReceiver()),
            'amount' => $transaction->getAmount()
        ];
    }
}
