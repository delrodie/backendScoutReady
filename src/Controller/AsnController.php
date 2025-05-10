<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/backend/asn')]
class AsnController extends AbstractController
{
    #[Route('/', name:'app_asn_list')]
    public function list(): Response
    {
        return $this->render('asn/list.html.twig');
    }

    #[Route('/new', name:'app_asn_new', methods: ['GET', 'POST'])]
    public function new(Request $request)
    {
        return $this->render('asn/new.html.twig');
    }
}
