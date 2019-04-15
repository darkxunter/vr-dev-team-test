<?php

namespace App\Exceptions\ExchangeRates;

class InvalidDateException extends \Exception
{
    public function __construct(\DateTime $requestDate, \DateTime $responseDate)
    {
        $message = "Response date ({$responseDate->format('Y-m-d')}) " .
            "is different from  request date ({$requestDate->format('Y-m-d')})";
        parent::__construct($message);
    }

}