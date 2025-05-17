<?php

declare(strict_types=1);

namespace App\Controller\Import;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ImportGroupeController extends AbstractController
{
    #[Route('/import-groupe', name: 'app_groupe_upload')]
    public function upload(): Response
    {
        return $this->render('import_groupe/index.html.twig');
    }
}
