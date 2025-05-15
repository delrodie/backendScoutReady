<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/error')]
class ErrorController extends AbstractController
{
    #[Route('/', name: 'app_error_page', methods: ['GET', 'POST'])]
    public function show(Request $request): Response
    {
        $statusCode = (int) $request->get('statusCode');
        $message = (string) $request->get('message');
        return $this->render('error/error_page.html.twig',[
            'status_code' => $statusCode,
            'message' => $message
        ]);
    }
}
