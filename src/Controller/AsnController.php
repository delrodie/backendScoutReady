<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\ApiKeyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/backend/asn')]
class AsnController extends AbstractController
{
    public function __construct(private readonly ApiKeyService $apiKeyService)
    {
    }

    #[Route('/', name:'app_asn_list')]
    public function list(): Response
    {
        try {
            $asns = $this->apiKeyService->fetchData('/api/asns');
        } catch(HttpExceptionInterface $e){
            return $this->redirectToRoute('app_error_page', [
                'statusCode' => $e->getStatusCode(),
                'message' => $e->getMessage()
            ]);
        }

        return $this->render('asn/list.html.twig',[
            'asns' => $asns,
        ]);
    }

    #[Route('/new', name:'app_asn_new', methods: ['GET', 'POST'])]
    public function new(Request $request)
    {
        return $this->render('asn/new.html.twig');
    }
}
