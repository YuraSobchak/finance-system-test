<?php
declare(strict_types=1);

namespace App\Controller;

use App\DataTransformer\TransactionDataTransformer;
use App\DTO\TransactionRequest;
use App\Entity\User;
use App\Factory\TransactionFactory;
use App\Service\TransactionService;
use Doctrine\DBAL\Exception as DoctrineException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class TransactionController extends AbstractController
{
    private TransactionService $transactionService;
    private EntityManagerInterface $em;

    public function __construct(TransactionService $transactionService, EntityManagerInterface $em)
    {
        $this->transactionService = $transactionService;
        $this->em = $em;
    }

    /**
     * @Route("/api/finance/transfer", methods={"POST"}, name="api.finance.transfer")
     * @param TransactionRequest $transactionRequest
     * @return JsonResponse
     * @throws DoctrineException
     */
    public function transfer(TransactionRequest $transactionRequest): JsonResponse
    {
        $receiver = $this->em->getRepository(User::class)->findByUsernameOrEmail($transactionRequest->receiver());
        $this->em->getConnection()->beginTransaction();
        try {
            $transaction = (new TransactionFactory())->create(
                $receiver,
                $transactionRequest->amount(),
                $this->getUser()
            );
            $this->em->persist($transaction);
            $this->transactionService->payment(
                $receiver,
                $transactionRequest->amount(),
                $this->getUser()
            );
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }

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