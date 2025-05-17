<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Services\ApiKeyService;
use App\Services\CacheDistrictService;
use App\Services\CacheGroupeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Mailer\Exception\HttpTransportException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/backend/groupe')]
class GroupeController extends AbstractController
{
    public function __construct(
        private readonly ApiKeyService        $apiKeyService,
        private readonly CacheGroupeService   $cacheGroupeService,
        private readonly CacheDistrictService $cacheDistrictService,
    )
    {
    }

    #[Route('/', name:'app_groupe_list')]
    public function list(): Response
    {
        try {
            $groupes = $this->cacheGroupeService->getAllGroupe();
        } catch (HttpExceptionInterface $e){
            $this->handleException($e);
        }

        return $this->render('groupe/list.html.twig',[
            'groupes' => $groupes,
        ]);
    }

    #[Route('/new', name:'app_groupe_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if (
            $request->isMethod('POST')
            && $this->isCsrfTokenValid('groupeAdd', $request->getPayload()->getString('_groupeCsrfToken'))
        ){
            $groupe_district = trim($request->get('groupe_district'));
            $groupe_nom = trim($request->get('groupe_nom'));

            if (empty($groupe_district) || empty($groupe_nom)){
                sweetalert()->success("Tous les champs sont obligatoires.");
                return $this->render('groupe/new.html.twig',[
                    'districts' => $this->cacheDistrictService->getAllDistrict()
                ]);
            }

            // Sauvegarde dans la base de données
            try {
                $this->apiKeyService->sendData('/api/groupes', [
                    'district' => (int) $groupe_district,
                    'paroisse' => (string) $groupe_nom
                ]);

                // Suppression du cache
                $this->cacheGroupeService->clearCacheGroupe();
                sweetalert()->success("Groupe a été enregistré avec succès!");
                return $this->redirectToRoute('app_groupe_list',[],Response::HTTP_SEE_OTHER);
            }catch (HttpExceptionInterface $e){
                return $this->handleException($e);
            }
        }

        return $this->render('groupe/new.html.twig',[
            'districts' => $this->cacheDistrictService->getAllDistrict()
        ]);
    }

    #[Route('/{id}/update', name:'app_groupe_update', methods: ['GET', 'POST'])]
    public function update(Request $request, $id): Response
    {
        $groupe = $this->getGroupeById($id);
        if ($groupe instanceof RedirectResponse){
            return $groupe;
        }

        if (
            $request->isMethod('POST')
            && $this->isCsrfTokenValid('groupeUpdate', $request->getPayload()->getString('_groupeCsrfToken'))
        ){
            $groupe_district = trim($request->get('groupe_district'));
            $groupe_nom = trim($request->get('groupe_nom'));

            $data = [
                'district' => (int) $groupe_district ?: $groupe['district']['id'],
                'paroisse' => (string) $groupe_nom ?: $groupe['paroisse'],
            ]; //dd($data);

            // Sauvegarde dans la base de données
            try {
                $this->apiKeyService->sendData('/api/groupes/'.$id, $data, 'PATCH');

                // Suppression du cache
                $this->cacheGroupeService->clearCacheGroupe();
                $this->cacheGroupeService->clearCacheGroupe((int)$id);
                sweetalert()->success("Le groupe '$groupe_nom' a été modifié avec succès!");
                return $this->redirectToRoute('app_groupe_list',[],Response::HTTP_SEE_OTHER);
            }catch (HttpExceptionInterface $e){
                return $this->handleException($e);
            }
        }

        return $this->render('groupe/update.html.twig',[
            'districts' => $this->cacheDistrictService->getAllDistrict(),
            'groupe' => $groupe
        ]);
    }

    #[Route('/{id}', name:'app_groupe_delete', methods: ['POST'])]
    public function delete(Request $request, $id)
    {
        $groupe = $this->getGroupeById($id);
        if ($groupe instanceof RedirectResponse){
            return $groupe;
        }

        if ($this->isCsrfTokenValid('groupeDelete'.(int)$id, $request->getPayload()->getString('_groupeCsrfToken')))
        {
            $this->cacheGroupeService->clearCacheGroupe();
            $this->cacheGroupeService->clearCacheGroupe((int) $id);

            try {
                $this->apiKeyService->sendData('/api/groupes/'.$id, [], 'DELETE');
            } catch (HttpExceptionInterface $e){
                return $this->handleException($e);
            }
        }

        return $this->redirectToRoute('app_groupe_list',[], Response::HTTP_SEE_OTHER);
    }

    private function handleException(HttpExceptionInterface|\Exception $e): Response
    {
        return $this->redirectToRoute('app_error_page', [
            'statusCode' => $e->getStatusCode(),
            'message' => $e->getMessage(),
        ]);
    }

    private function getGroupeById($id)
    {
        try {
            return $this->cacheGroupeService->getGroupeById($id);
        }catch (HttpExceptionInterface $e){
            return $this->handleException($e);
        }
    }
}
