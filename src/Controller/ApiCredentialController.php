<?php

namespace App\Controller;

use App\Entity\ApiCredential;
use App\Form\ApiCredentialForm;
use App\Repository\ApiCredentialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/api-credential')]
final class ApiCredentialController extends AbstractController
{
    #[Route(name: 'app_api_credential_index', methods: ['GET'])]
    public function index(ApiCredentialRepository $apiCredentialRepository): Response
    {
        return $this->render('api_credential/index.html.twig', [
            'api_credentials' => $apiCredentialRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_api_credential_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $apiCredential = new ApiCredential();
        $form = $this->createForm(ApiCredentialForm::class, $apiCredential);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($apiCredential);
            $entityManager->flush();

            return $this->redirectToRoute('app_api_credential_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('api_credential/new.html.twig', [
            'api_credential' => $apiCredential,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_api_credential_show', methods: ['GET'])]
    public function show(ApiCredential $apiCredential): Response
    {
        return $this->render('api_credential/show.html.twig', [
            'api_credential' => $apiCredential,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_api_credential_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ApiCredential $apiCredential, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ApiCredentialForm::class, $apiCredential);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_api_credential_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('api_credential/edit.html.twig', [
            'api_credential' => $apiCredential,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_api_credential_delete', methods: ['POST'])]
    public function delete(Request $request, ApiCredential $apiCredential, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$apiCredential->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($apiCredential);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_api_credential_index', [], Response::HTTP_SEE_OTHER);
    }
}
