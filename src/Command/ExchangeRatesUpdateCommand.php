<?php

namespace App\Command;

use App\Service\ExchangeRatesService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExchangeRatesUpdateCommand extends Command
{
    protected static $defaultName = 'app:exchange-rates:update';

    /** @var ExchangeRatesService $exchangeRatesService */
    private $exchangeRatesService;

    public function __construct(ExchangeRatesService $exchangeRatesService)
    {
        parent::__construct(null);
        $this->exchangeRatesService = $exchangeRatesService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Updates exchange rates')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $isSuccess = $this->exchangeRatesService->updateRates();

        if ($isSuccess) {
            $io->success('Exchange rates were successfully updated!');
            return;
        }

        $io->error('Unable to update exchange rates. Please try again later.');
    }
}
