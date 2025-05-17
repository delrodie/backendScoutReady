<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Services\ApiKeyService;
use App\Services\CacheDistrictService;
use App\Services\CacheRegionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/backend/district')]
class DistrictController extends AbstractController
{
    public function __construct(private readonly CacheDistrictService $cacheDistrictService, private readonly CacheRegionService $cacheRegionService, private readonly ApiKeyService $apiKeyService)
    {
    }

    #[Route('/', name:'app_district_list')]
    public function list(): Response
    {
        try {
            $districts = $this->cacheDistrictService->getAllDistrict();
        }catch(HttpExceptionInterface $e){
            $this->handleException($e);
        }

        return $this->render('district/list.html.twig',[
            'districts' => $districts,
        ]);
    }

    #[Route('/new', name: 'app_district_new', methods: ['GET','POST'])]
    public function new(Request $request): Response
    {
        if (
            $request->isMethod('POST')
            && $this->isCsrfTokenValid('districtAdd', $request->getPayload()->getString('_districtCsrfToken'))
        ) {
                $district_region = trim($request->get('district_region'));
                $district_nom = trim($request->get('district_nom'));

                // Gestion des champs obligatoire
                if (empty($district_region) || empty($district_nom)){
                    sweetalert()->error("Attention! Tous les champs sont obligatoires");
                    return $this->render('district/new.html.twig',[
                        'regions' => $this->cacheRegionService->getAllRegion(),
                    ]);
                }

                // Sauvegarde dans la base de données
            try {
                $this->apiKeyService->sendData('/api/districts',[
                    'region' => (int) $district_region,
                    'nom' => (string) $district_nom
                ]);

                // Suppression du cache
                $this->cacheDistrictService->clearAllDistrictCache();
                sweetalert()->success("District enregistré avec succès!");

                return $this->redirectToRoute('app_district_list',[], Response::HTTP_SEE_OTHER);
            }catch (HttpExceptionInterface $e){
                    $this->handleException($e);
            }
        }

        return $this->render('district/new.html.twig',[
            'regions' => $this->cacheRegionService->getAllRegion()
        ]);
    }

    #[Route('/{id}/update', name: 'app_district_update', methods: ['GET','POST'])]
    public function update(Request $request, $id)
    {
        $district = $this->getDistrictById($id);
        if ($district instanceof RedirectResponse){
            return $district;
        }

        if (
            $request->isMethod('POST')
            && $this->isCsrfTokenValid('districtAdd', $request->getPayload()->getString('_districtCsrfToken'))
        )
        {
            $district_region = trim($request->get('district_region'));
            $district_nom = trim($request->get('district_nom'));

            $data = [
                'region' => (int) $district_region ?: $district['region']['id'],
                'nom' => (string) $district_nom ?: $district['nom']
            ];

            try {
                $this->apiKeyService->sendData('/api/districts/'.$id, $data, 'PATCH');

                //Suppression du cache global
                $this->cacheDistrictService->clearAllDistrictCache();
                $this->cacheDistrictService->clearDistrictCacheById($id);

                sweetalert()->success("Mise a jour effectuée avec succès!");
                return $this->redirectToRoute('app_district_list',[], Response::HTTP_SEE_OTHER);
            } catch (HttpExceptionInterface $e){
                return $this->handleException($e);
            }
        }

        return $this->render('district/update.html.twig',[
            'regions' => $this->cacheRegionService->getAllRegion(),
            'district' => $district
    ]);
    }

    #[Route('/{id}', name: 'app_district_delete', methods: ['POST'])]
    public function delete(Request $request, $id)
    {
        $district = $this->getDistrictById($id);
        if ($district instanceof RedirectResponse){
            return $district;
        }

        if($this->isCsrfTokenValid('districtDelete'.$id,  $request->getPayload()->getString('_districtCsrfToken'))){
            try {
                $this->apiKeyService->sendData('/api/districts/'.$id, [],'DELETE');
                $this->cacheDistrictService->clearAllDistrictCache();
                $this->cacheDistrictService->clearDistrictCacheById($id);
            } catch (HttpExceptionInterface $e){
                $this->cacheDistrictService->clearAllDistrictCache();
                $this->cacheDistrictService->clearDistrictCacheById($id);
                return $this->handleException($e);
            }
        }

        return $this->redirectToRoute('app_district_list',[], Response::HTTP_SEE_OTHER);
    }

    private function handleException(HttpExceptionInterface|\Exception $e): Response
    {
        return $this->redirectToRoute('app_error_page', [
            'statusCode' => $e->getStatusCode(),
            'message' => $e->getMessage(),
        ]);
    }

    private function getDistrictById($id)
    {
        try {
            return $this->cacheDistrictService->getDistrictById($id);
        }catch(HttpExceptionInterface $e){
            $this->handleException($e);
        }
    }
}
