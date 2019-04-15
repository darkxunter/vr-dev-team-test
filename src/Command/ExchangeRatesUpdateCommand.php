<?php

namespace App\Command;

use App\Entity\ExchangeRate;
use App\Entity\ExchangeRatesResponse;
use App\Interfaces\ExchangeRatesProviderInterface as ExchangeRatesProvider;
use App\Repository\ExchangeRateRepository;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ExchangeRatesUpdateCommand extends Command
{
    protected static $defaultName = 'app:exchange-rates:update';

    /** @var ExchangeRatesProvider $exchangeRatesProvider */
    private $exchangeRatesProvider;

    private $supportedCurrencies;

    /** @var EntityManager */
    private $em;

    public function __construct(ContainerInterface $container, ExchangeRatesProvider $exchangeRatesProvider, $supportedCurrencies)
    {
        parent::__construct(null);
        $this->exchangeRatesProvider = $exchangeRatesProvider;
        $this->supportedCurrencies = $supportedCurrencies;
        $this->em = $container->get('doctrine')->getEntityManager();
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

        try {
            $ratesResponse = $this->getLastRates();

            $ratesToCheck = \array_filter($ratesResponse->getPairs(), function($code) {
                return \in_array($code, $this->supportedCurrencies);
            }, ARRAY_FILTER_USE_KEY);

            /** @var ExchangeRateRepository $repo */
            $repo = $this->em->getRepository(ExchangeRate::class);
            $existedRatesForResponseDate = $repo->findAllByDateAndCodes($ratesResponse->getDate(), \array_keys($ratesToCheck));

            foreach ($existedRatesForResponseDate as $exchangeRate) {
                $exchangeRate->setRate($ratesToCheck[$exchangeRate->getCurrencyCode()]);
                unset($ratesToCheck[$exchangeRate->getCurrencyCode()]);
            }
            $this->createNewRates($ratesResponse->getDate(), $ratesToCheck);
            $this->em->flush();

            $this->removeCachedRates();

            $io->success('Exchange rates were successfully updated!');
        } catch (ClientException $e) {
            $io->error('Unable to update exchange rates. Please try again later.');
        }
    }

    private function getLastRates(): ExchangeRatesResponse
    {
        return $this->exchangeRatesProvider->getLastRates();
    }

    private function createNewRates(\DateTime $date, $rates): void
    {
        foreach ($rates as $code => $rate) {
            $exchangeRate = new ExchangeRate();
            $exchangeRate->setCurrencyCode($code);
            $exchangeRate->setRate($rate);
            $exchangeRate->setDate($date);
            $this->em->persist($exchangeRate);
        }
    }

    private function removeCachedRates(): void
    {
        $cache = new FilesystemAdapter();
        $cache->deleteItem('last_exchange_rates');
    }
}
