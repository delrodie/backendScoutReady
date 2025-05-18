<?php

declare(strict_types=1);

namespace App\Controller\Import;

use App\Services\Cache\CacheGroupeService;
use App\Services\Import\GroupeExcelImporter;
use App\Services\UserActionLogger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/backend/groupe/upload')]
class ImportGroupeController extends AbstractController
{
    public function __construct(
        private readonly CacheGroupeService $cacheGroupeService,
        private readonly UserActionLogger   $userActionLogger,
    )
    {
    }

    #[Route('/', name: 'app_groupe_upload', methods: ['GET','POST'])]
    public function upload(Request $request, GroupeExcelImporter $importer): Response
    {
        if (
            $request->isMethod('POST')
            && $this->isCsrfTokenValid('groupeImported', $request->getPayload()->getString('_groupeCsrfToken'))
        )
        {
            $file = $request->files->get('groupe_file'); //dd($file);

            if (!$file || $file->getClientOriginalExtension() !== 'xlsx') {
                sweetalert()->error("Echèc, Veuillez uploader un fichierExcel (.xlsx)");
                return $this->redirectToRoute('app_groupe_upload');
            }

            try {
                $result = $importer->import($file);
                sweetalert()->success("Importation terminée: {$result['imported']} districts ajoutés, {$result['skipped']} ignorés");

                if (!empty($result['errors'])) {
                    foreach ($result['errors'] as $error) {
                        sweetalert()->info($error);
                    }
                }

                $this->userActionLogger->log("Importation groupe",[
                    'action' => "Importation terminée: {$result['imported']} groupes ajoutés, {$result['skipped']} ignorés"
                ]);

                // Suppression du cache
                $this->cacheGroupeService->clearCacheGroupe();

                return $this->redirectToRoute('app_groupe_list');
            } catch (\Throwable $e){
                sweetalert()->error("Erreur d'importation : {$e->getMessage()}");
            }
        }
        return $this->render('groupe/upload.html.twig');
    }
}
