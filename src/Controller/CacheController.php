<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\CacheAsnService;
use App\Services\CacheDistrictService;
use App\Services\CacheRegionService;
use App\Services\UtilityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cache')]
class CacheController extends AbstractController
{
    public function __construct(
        private readonly CacheAsnService $cacheAsnService,
        private readonly CacheRegionService $cacheRegionService,
        private readonly CacheDistrictService $cacheDistrictService,
    )
    {
    }

    #[Route('/', name: 'app_cache_delete_all')]
    public function all(): Response
    {
        $this->cacheAsnService->invalidateAsnCache(); // Cache des ASNs
        $this->cacheRegionService->clearAllRegionCache(); // Cache des region
        $this->cacheDistrictService->clearAllDistrictCache(); // Cache des districts

        sweetalert()->success("La cache a été vidé avec succès!");

        return $this->redirectToRoute('app_dashboard',[], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{module}', name: 'app_cache_delete_module', methods: ['GET'])]
    public function module(Request $request, string $module): Response
    {
        match ($module){
            'asn' => $this->cacheAsnService->invalidateAsnCache(),
            'region' => $this->cacheRegionService->clearAllRegionCache(),
            'district' => $this->cacheDistrictService->clearAllDistrictCache(),
        };

        sweetalert()->success("Le cache a été supprimé avec succès!");

        $url_precedent = $request->headers->get('referer');
        return $this->redirect($url_precedent ?? $this->generateUrl('app_dashboard'));
    }
}
