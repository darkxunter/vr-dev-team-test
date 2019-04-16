<?php

namespace App\Tests;

use App\Entity\ExchangeRate;
use App\Service\ExchangeRatesService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class CalculatorTest extends WebTestCase
{
    /** @var ExchangeRatesService $exchangeRatesService */
    private $exchangeRatesService;

    public function setUp(): void
    {
        self::bootKernel();

        $this->exchangeRatesService = $this->getExchangeRatesService();
        $this->emptyExchangeRatesTable();
        $this->emptyCache();
    }

    public function testHomepageCalculator(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('There is no rates', $crawler->filter('p')->text());

        $this->exchangeRatesService->updateRates();

        $crawler = $client->request('GET', '/');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Rates updated at', $crawler->filter('h4')->text());
    }

    private function emptyExchangeRatesTable(): void
    {
        $connection = $this->getEntityManager()->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();

        if ($databasePlatform->supportsForeignKeyConstraints()) {
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
        }

        $query = $databasePlatform->getTruncateTableSQL('exchange_rate');
        $connection->executeUpdate($query);

        if ($databasePlatform->supportsForeignKeyConstraints()) {
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    private function getExchangeRatesService(): ExchangeRatesService
    {
        return self::$container->get(ExchangeRatesService::class);
    }

    private function getEntityManager(): EntityManager
    {
        return self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    private function emptyCache(): void
    {
        $cache = new FilesystemAdapter();
        $cache->deleteItem('last_exchange_rates');
    }
}
