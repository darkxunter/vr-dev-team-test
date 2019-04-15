<?php

namespace App\Interfaces;

use App\Entity\ExchangeRatesResponse;

interface ExchangeRatesProviderInterface
{
    public function getLastRates(): ExchangeRatesResponse;

    public function getRatesForDate(\DateTime $date): ExchangeRatesResponse;
}