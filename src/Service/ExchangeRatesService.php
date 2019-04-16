<?php

namespace App\Service;

use App\Entity\ExchangeRate;
use App\Interfaces\ExchangeRatesProviderInterface as ExchangeRatesProvider;
use App\Repository\ExchangeRateRepository;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class ExchangeRatesService
{
    private const LAST_RATES_CACHE_KEY = 'last_exchange_rates';

    /** @var  ExchangeRatesProvider $exchangeRatesProvider */
    private $exchangeRatesProvider;

    /** @var EntityManagerInterface $em */
    private $em;

    /** @var ExchangeRateRepository $repo */
    private $repo;

    /** @var FilesystemAdapter $cache */
    private $cache;

    /** @var  string[] $supportedCurrencies */
    private $supportedCurrencies;

    public function __construct(ContainerInterface $container, ExchangeRatesProvider $provider, $supportedCurrencies)
    {
        $this->em = $container->get('doctrine')->getManager();
        $this->repo = $this->em->getRepository(ExchangeRate::class);
        $this->exchangeRatesProvider = $provider;
        $this->cache = new FilesystemAdapter();
        $this->supportedCurrencies = $supportedCurrencies;
    }

    public function getSupportedCurrencies(): array
    {
        return $this->supportedCurrencies;
    }

    /**
     * @return ExchangeRate[]
     */
    public function getLastRates(): array
    {
        return $this->cache->get(self::LAST_RATES_CACHE_KEY, function(ItemInterface $item) {
            $item->expiresAfter(3600);

            $rates = $this->repo->findAllLastDate();
            \usort($rates, function(ExchangeRate $a, ExchangeRate $b) {
                $aIndex = \array_search($a->getCurrencyCode(), $this->supportedCurrencies, true);
                $bIndex = \array_search($b->getCurrencyCode(), $this->supportedCurrencies, true);
                return  $aIndex <=> $bIndex;
            });
            return $rates;
        });
    }

    public function updateRates(): bool
    {
        try {
            $ratesResponse = $this->exchangeRatesProvider->getLastRates();

            $ratesToCheck = \array_filter($ratesResponse->getPairs(), function($code) {
                return \in_array($code, $this->supportedCurrencies, true);
            }, ARRAY_FILTER_USE_KEY);

            $existedRatesForResponseDate = $this->repo
                ->findAllByDateAndCodes($ratesResponse->getDate(), \array_keys($ratesToCheck));

            foreach ($existedRatesForResponseDate as $exchangeRate) {
                $exchangeRate->setRate($ratesToCheck[$exchangeRate->getCurrencyCode()]);
                unset($ratesToCheck[$exchangeRate->getCurrencyCode()]);
            }
            $this->createNewRates($ratesResponse->getDate(), $ratesToCheck);
            $this->em->flush();

            $this->removeCachedRates();
            return true;
        } catch (ClientException $e) {
            return false;
        }
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
        $cache->deleteItem(self::LAST_RATES_CACHE_KEY);
    }
}