<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Services\ApiKeyService;
use App\Services\Cache\CacheAsnService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/backend/asn')]
class AsnController extends AbstractController
{
    public function __construct(
        private readonly ApiKeyService $apiKeyService,
        private readonly CacheAsnService $cacheAsnService,
    ) {}

    #[Route('/', name: 'app_asn_list', methods: ['GET'])]
    public function list(): Response
    {
        try {
//            $asns = $this->apiKeyService->fetchData('/api/asns');
            $asns = $this->cacheAsnService->getAllAsns();
        } catch (HttpExceptionInterface $e) {
            return $this->handleException($e);
        }

        return $this->render('asn/list.html.twig', [
            'asns' => $asns,
        ]);
    }

    #[Route('/new', name: 'app_asn_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST') && $this->isCsrfTokenValid('asnAdd', $request->getPayload()->getString('_asnCsrfToken'))) {
            $sigle = trim((string)$request->get('asn_sigle'));
            $nom = trim((string)$request->get('asn_nom'));

            if (empty($sigle) || empty($nom)) {
                sweetalert()->error("Tous les champs sont obligatoires.", [], 'Erreur');
                return $this->render('asn/new.html.twig');
            }

            try {
                $response = $this->apiKeyService->sendData('/api/asns', [
                    'sigle' => $sigle,
                    'nom' => $nom,
                ], 'POST');

                // Suppression des caches après la modification
                $this->cacheAsnService->invalidateAsnCache();

                sweetalert()->success("L'ASN « {$response['sigle']} » a été ajoutée avec succès.", [], 'Succès');
                return $this->redirectToRoute('app_asn_list', [], Response::HTTP_SEE_OTHER);
            } catch (HttpExceptionInterface $e) {
                return $this->handleException($e);
            }
        }

        return $this->render('asn/new.html.twig');
    }

    #[Route('/{id}/update', name: 'app_asn_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        $asn = $this->getAsnById($id);
        if ($asn instanceof RedirectResponse) {
            return $asn;
        }

        if ($request->isMethod('POST') && $this->isCsrfTokenValid('asnUpdate', $request->getPayload()->getString('_asnCsrfToken'))) {
            $sigle = trim((string)$request->get('asn_sigle'));
            $nom = trim((string)$request->get('asn_nom'));

            $data = [
                'sigle' => $sigle ?: $asn['sigle'],
                'nom' => $nom ?: $asn['nom'],
            ];

            try {
                $response = $this->apiKeyService->sendData('/api/asns/' . $asn['id'], $data, 'PATCH');

                // Suppression des caches après la modification
                $this->cacheAsnService->invalidateAsnById($id);
                $this->cacheAsnService->invalidateAsnCache();

                sweetalert()->success("L'ASN « {$response['sigle']} » a été modifiée avec succès.", [], 'Succès');
                return $this->redirectToRoute('app_asn_list', [], Response::HTTP_SEE_OTHER);
            } catch (HttpExceptionInterface $e) {
                return $this->handleException($e);
            }
        }

        return $this->render('asn/update.html.twig', [
            'asn' => $asn,
        ]);
    }

    #[Route('/{id}', name: 'app_asn_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        $asn = $this->getAsnById($id);
        if ($asn instanceof RedirectResponse) {
            return $asn;
        }

        if ($this->isCsrfTokenValid('asnDelete' . $id, $request->getPayload()->getString('_asnCsrfToken'))) {
            try {
                $this->apiKeyService->sendData('/api/asns/' . $id, $asn, 'DELETE');
                $this->cacheAsnService->invalidateAsnById($id); // Suppression du cache après suppression
                $this->cacheAsnService->invalidateAsnCache(); // Suppression de la liste en cache

            } catch (HttpExceptionInterface $e) {
                $this->cacheAsnService->invalidateAsnById($id); // Suppression du cache après suppression
                $this->cacheAsnService->invalidateAsnCache();

                return $this->handleException($e);
            }
        }

        return $this->redirectToRoute('app_asn_list', [], Response::HTTP_SEE_OTHER);
    }

    private function getAsnById(int $id): array|RedirectResponse
    {
        try {
//            return $this->apiKeyService->fetchData('/api/asns/' . $id);
            return $this->cacheAsnService->getAsnById($id);
        } catch (HttpExceptionInterface $e) {
            return $this->handleException($e);
        }
    }

    private function handleException(HttpExceptionInterface $e): RedirectResponse
    {
        return $this->redirectToRoute('app_error_page', [
            'statusCode' => $e->getStatusCode(),
            'message' => $e->getMessage(),
        ]);
    }
}
