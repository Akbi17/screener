<?php

namespace App\Controller;

use App\Service\BinanceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

class MainController extends AbstractController
{

    private $binanceService;

    public function __construct(BinanceService $binanceService)
    {
        $this->binanceService = $binanceService;
    }

    #[Route('/', name: 'top_volume_coins',)]
    public function topVolumeCoins(): Response
    {
        $topVolumeCoins = $this->binanceService->getTopVolumeCoinsInUSDT();

        return $this->render('index.html.twig', [
            'coins' => $topVolumeCoins,
        ]);
    }

    #[Route('/top-lowest-coins', name: 'top_lowest_coins',)]
    public function topLowest(): Response
    {
        $topDecliningCoins = $this->binanceService->getTopDecliningCoins();

        return $this->render('index.html.twig', [
            'coins' => $topDecliningCoins,
        ]);
    }

    #[Route('/top-raise-coins', name: 'top_raise_coins',)]
    public function topRaise(): Response
    {
        $topDecliningCoins = $this->binanceService->getTopRaisingCoins();

        return $this->render('index.html.twig', [
            'coins' => $topDecliningCoins,
        ]);
    }

    #[Route('/top-transactions-coins', name: 'top_transactions_coins',)]
    public function topTransations(): Response
    {
        $topDecliningCoins = $this->binanceService->getTopTransactionCoins();

        return $this->render('index.html.twig', [
            'coins' => $topDecliningCoins,
        ]);
    }
}
