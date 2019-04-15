<?php

namespace App\Controller;

use App\Service\LastExchangeRatesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /** @var LastExchangeRatesService $lastExchangeRatesService */
    private $lastExchangeRatesService;

    public function __construct(LastExchangeRatesService $lastExchangeRatesService)
    {
        $this->lastExchangeRatesService = $lastExchangeRatesService;
    }

    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        $rates = $this->lastExchangeRatesService->getLastRates();
        return $this->render('home/index.html.twig', [
            'rates' => $rates,
        ]);
    }
}
