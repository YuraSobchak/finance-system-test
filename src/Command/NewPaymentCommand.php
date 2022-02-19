<?php

namespace App\Command;

use App\Entity\User;
use App\Factory\TransactionFactory;
use App\Service\TransactionService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:new-payment',
    description: 'Add a short description for your command',
)]
class NewPaymentCommand extends Command
{
    private EntityManagerInterface $em;
    private TransactionService $transactionService;

    public function __construct(EntityManagerInterface $em, TransactionService $transactionService)
    {
        $this->em = $em;
        $this->transactionService = $transactionService;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('login', InputArgument::REQUIRED, 'Username/email is required')
            ->addArgument('amount', InputArgument::REQUIRED, 'Amount is required')
        ;
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $login = $input->getArgument('login');
        $amount = $input->getArgument('amount');

        $io->note(sprintf('You passed an login: %s', $login));
        $io->note(sprintf('You passed an amount: %s', $amount));

        $receiver = $this->em->getRepository(User::class)->findByUsernameOrEmail($login);
        $this->em->getConnection()->beginTransaction();
        try {
            // create transaction
            $transaction = (new TransactionFactory())->create($receiver, $amount);
            $this->em->persist($transaction);
            // calculate user balance
            $this->transactionService->payment($receiver, $amount);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }

        $io->success('Success payment!');

        return Command::SUCCESS;
    }
}
