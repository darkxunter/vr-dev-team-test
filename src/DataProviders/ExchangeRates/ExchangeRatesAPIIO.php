<?php

namespace App\DataProviders\ExchangeRates;

use App\Entity\ExchangeRatesResponse;
use App\Exceptions\ExchangeRates\InvalidDateException;
use App\Interfaces\ExchangeRatesProviderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

final class ExchangeRatesAPIIO implements ExchangeRatesProviderInterface
{
    private const ENDPOINT_URL = 'https://api.exchangeratesapi.io/';

    /**
     * @return ExchangeRatesResponse
     * @throws ClientException
     */
    public function getLastRates(): ExchangeRatesResponse
    {
        try {
            $response = $this->getAPIResponse();
            $response['date'] = new \DateTime($response['date']);
            return $this->makeExchangeRatesResponseFromAPIResponse($response);
        } catch (ClientException $e) {
            throw $e;
        }
    }

    /**
     * @param \DateTime $date
     * @return ExchangeRatesResponse
     * @throws InvalidDateException, ClientException
     */
    public function getRatesForDate(\DateTime $date): ExchangeRatesResponse
    {
        try {
            $response = $this->getAPIResponse($date);
            $response['date'] = new \DateTime($response['date']);
            $diffInDays = $date->diff($response['date'])->d; //Check if API response date is same as requested
            if ($diffInDays > 0) {
                throw new InvalidDateException($date, $response['date']);
            }
            return $this->makeExchangeRatesResponseFromAPIResponse($response);
        } catch (ClientException $e) {
            throw $e;
        }
    }

    private function getAPIResponse(\DateTime $date = null): array
    {
        $dateString = null !== $date
            ? $date->format('Y-m-d')
            : (new \DateTime('tomorrow'))->format('Y-m-d'); //don't use "/latest" endpoint
        $url = self::ENDPOINT_URL . $dateString;                         //causes recursive redirect sometimes
        $client = new Client();                                          //request with tomorrow date returns last rates
        $response = $client->get($url);
        return json_decode((string)$response->getBody(), true);
    }

    private function makeExchangeRatesResponseFromAPIResponse(array $response): ExchangeRatesResponse
    {
        $response['rates'][$response['base']] = 1;
        return new ExchangeRatesResponse($response['date'], $response['rates']);
    }

}