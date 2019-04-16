<?php

namespace App\Controller;

use App\Service\ExchangeRatesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /** @var ExchangeRatesService $lastExchangeRatesService */
    private $exchangeRatesService;

    public function __construct(ExchangeRatesService $lastExchangeRatesService)
    {
        $this->exchangeRatesService = $lastExchangeRatesService;
    }

    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        $rates = $this->exchangeRatesService->getLastRates();
        return $this->render('home/index.html.twig', [
            'rates' => $rates,
        ]);
    }
}
