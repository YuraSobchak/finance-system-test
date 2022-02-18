<?php
declare(strict_types=1);

namespace App\Controller;

use App\DataTransformer\TransactionDataTransformer;
use App\Service\TransactionService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class TransactionController extends AbstractController
{
    private TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * @Route("/api/finance/transfer", methods={"POST"}, name="api.finance.transfer")
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function transfer(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $transaction = $this->transactionService->payment(
            $data['receiver'],
            (float)$data['amount'],
            $this->getUser()
        );

        return new JsonResponse([
            'status' => 'ok',
            'data' => TransactionDataTransformer::transactionToArray($transaction),
        ]);
    }

    /**
     * @Route("/api/finance", methods={"GET"}, name="api.finance.list")
     * @return JsonResponse
     * @throws Exception
     */
    public function list(): JsonResponse
    {
        $payments = $this->getUser()->getPayments();
        $transactions = $this->getUser()->getTransactions();

        return new JsonResponse([
            'status' => 'ok',
            'data' => [
                'payments' => TransactionDataTransformer::transactionsToArray($payments),
                'transactions' => TransactionDataTransformer::transactionsToArray($transactions)
            ],
        ]);
    }
}