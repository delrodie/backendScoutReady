<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Services\Cache\CacheScoutService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/backend/scout')]
class ScoutController extends AbstractController
{
    public function __construct(private readonly CacheScoutService $cacheScoutService)
    {
    }

    #[Route('/', name: 'app_scout_list')]
    public function list(): Response
    {
        try {
            $scouts = $this->cacheScoutService->getAllScoutCache();
        }catch (HttpExceptionInterface $e){
            $this->handleException($e);
        }
        return $this->render('scout/list.html.twig',[
            'scouts' => $scouts
        ]);
    }

    #[Route('/new', name: 'app_scout_new',methods: ['GET','POST'])]
    public function new(Request $request)
    {

    }

    #[Route('/{id}', name: 'app_scout_profile', methods: ['GET'])]
    public function profile($id): Response
    {
        return $this->render('scout/profile.html.twig',[
            'scout' => $this->getScoutById($id)
        ]);
    }

    #[Route('/{id}/update', name: 'app_scout_update', methods: ['GET','POST'])]
    public function update(Request $request, $id): Response
    {
        return $this->render('scout/update.html.twig',[
            'scout' => $this->getScoutById($id)
        ]);
    }

    private function handleException(HttpExceptionInterface|\Exception $e): Response
    {
        return $this->redirectToRoute('app_error_page', [
            'statusCode' => $e->getStatusCode(),
            'message' => $e->getMessage(),
        ]);
    }

    private function getScoutById($id)
    {
        try {
            return $this->cacheScoutService->getScoutCacheById($id);
        } catch(HttpExceptionInterface $e) {
            $this->handleException($e);
        }
    }
}
