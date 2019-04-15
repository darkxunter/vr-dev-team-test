<?php

namespace App\Service;

use App\Entity\ExchangeRate;
use App\Repository\ExchangeRateRepository;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class LastExchangeRatesService
{
    /** @var ExchangeRateRepository $repo */
    private $repo;

    /** @var FilesystemAdapter $cache */
    private $cache;

    public function __construct(ContainerInterface $container)
    {
        $this->repo = $container->get('doctrine')->getRepository(ExchangeRate::class);
        $this->cache = new FilesystemAdapter();
    }

    /**
     * @return ExchangeRate[]
     */
    public function getLastRates()
    {
        return $this->cache->get('last_exchange_rates', function(ItemInterface $item) {
            $item->expiresAfter(3600);

            return $this->repo->findAllLastDate();
        });
    }
}