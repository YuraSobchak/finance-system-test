<?php

namespace App\Command;

use App\Service\TransactionService;
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
    private TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
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

        // new transaction and calculate user balance
        $this->transactionService->payment($login, (float)$amount);

        $io->success('Success payment!');

        return Command::SUCCESS;
    }
}
