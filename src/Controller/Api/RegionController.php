<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Services\ApiKeyService;
use App\Services\CacheAsnService;
use App\Services\CacheRegionService;
use Doctrine\ORM\Cache\Region;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/backend/region')]
class RegionController extends AbstractController
{
    public function __construct(private readonly CacheRegionService $cacheRegionService, private readonly CacheAsnService $cacheAsnService, private readonly ApiKeyService $apiKeyService)
    {
    }

    #[Route('/', name: 'app_region_list')]
    public function list(): Response
    {
        try {
            $regions = $this->cacheRegionService->getAllRegion();
        } catch(HttpExceptionInterface $e){
            $this->handleException($e);
        }
        //dd($regions);
        return $this->render('region/list.html.twig',[
            'regions' => $regions
        ]);
    }

    #[Route('/new', name: 'app_region_new', methods: ['GET','POST'])]
    public function new(Request $request)
    {
        if (
            $request->isMethod('POST')
            && $this->isCsrfTokenValid('regionAdd', $request->getPayload()->getString('_regionCsrfToken'))
        ){
            $region_asn = trim($request->get('region_asn'));
            $region_nom = trim($request->get('region_nom'));
            $region_symbolique = trim($request->get('region_symbolique'));

            // Gestion des champs oblgatoire
            if (empty($region_nom) || empty($region_asn)){
                sweetalert("Les champs ASN et NOM sont obligatoires");
                return $this->render('region/new.html.twig',['asns' => $this->cacheAsnService->getAllAsns()]);
            }

            // Sauvegarde dans la base de données
            try{
                $response = $this->apiKeyService->sendData('/api/regions',[
                    'asn' => (int) $region_asn,
                    'nom' => (string) $region_nom,
                    'symbolique' => (string) $region_symbolique
                ]);

                // Suppression du cache gloabl après sauvegarde
                $this->cacheRegionService->clearAllRegionCache();

                sweetalert()->success("La région {$response['nom']} a été enregistrée avec succès!");
                return $this->redirectToRoute('app_region_list',[], Response::HTTP_SEE_OTHER);
            }catch (HttpExceptionInterface $e){
                return $this->handleException($e);
            }
        }
        return $this->render('region/new.html.twig', [
            'asns' => $this->cacheAsnService->getAllAsns()
        ]);
    }

    #[Route('/{id}/update', name:'app_region_update', methods: ['GET','POST'])]
    public function update(Request $request, $id)
    {
        $region = $this->getRegionById($id);
        if ($region instanceof Region){
            return $region;
        }

        if (
            $request->isMethod('POST')
            && $this->isCsrfTokenValid('regionUpdate', $request->getPayload()->getString('_regionCsrfToken'))
        ){
            $region_asn = trim($request->get('region_asn'));
            $region_nom = trim($request->get('region_nom'));
            $region_symbolique = trim($request->get('region_symbolique'));

            $data = [
                'asn' => (int) $region_asn ?: $region['asn']['id'],
                'nom' => $region_nom ?: $region['nom'],
                'symbolique' => $region_symbolique ?: $region['symbolique']
            ];

            // Sauvegarde dans la base de données
            try{
                $response = $this->apiKeyService->sendData('/api/regions/'.$region['id'],$data, 'PATCH');

                // Suppression du cache gloabl après sauvegarde
                $this->cacheRegionService->clearAllRegionCache();
                $this->cacheRegionService->clearRegionCacheById($id);

                sweetalert()->success("La région {$response['nom']} a été Modifiée avec succès!");
                return $this->redirectToRoute('app_region_list',[], Response::HTTP_SEE_OTHER);
            }catch (HttpExceptionInterface $e){
                return $this->handleException($e);
            }
        }

        return $this->render('region/update.html.twig',[
            'region' => $region,
            'asns' => $this->cacheAsnService->getAllAsns()
        ]);
    }

    #[Route('/{id}', name: 'app_region_delete', methods: ['POST'])]
    public function delete(Request $request, $id)
    {
        $region = $this->getRegionById($id);
        if ($region instanceof Region){
            return $region;
        }

        if ($this->isCsrfTokenValid('regionDelete'.$id,  $request->getPayload()->getString('_regionCsrfToken'))){
            try {
                $this->apiKeyService->sendData('/api/regions/'.$id, $region, 'DELETE');
                $this->cacheRegionService->clearAllRegionCache();
                $this->cacheRegionService->clearRegionCacheById($id);
            }catch (HttpExceptionInterface $e){
                $this->cacheRegionService->clearAllRegionCache();
                $this->cacheRegionService->clearRegionCacheById($id);

                $this->handleException($e);
            }
        }

        return $this->redirectToRoute('app_region_list',[], Response::HTTP_SEE_OTHER);
    }

    private function getRegionById($id)
    {
        try {
            return $this->cacheRegionService->getRegionById($id);
        } catch(HttpExceptionInterface $e){
            return $this->handleException($e);
        }
    }

    private function handleException(HttpExceptionInterface|\Exception $e): Response
    {
        return $this->redirectToRoute('app_error_page', [
            'statusCode' => $e->getStatusCode(),
            'message' => $e->getMessage(),
        ]);
    }
}
