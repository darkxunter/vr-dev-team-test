<?php

namespace App\Entity;

class ExchangeRatesResponse
{
    /** @var \DateTime */
    private $date;

    /** @var array $pairs Code => rate */
    private $pairs;

    public function __construct(\DateTime $date, array $pairs)
    {
        $this->date = $date;
        $this->pairs = $pairs;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function getPairs(): array
    {
        return $this->pairs;
    }
}