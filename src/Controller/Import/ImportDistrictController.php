<?php

declare(strict_types=1);

namespace App\Controller\Import;

use App\Services\Cache\CacheDistrictService;
use App\Services\Import\DistrictExcelImporter;
use App\Services\UserActionLogger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/backend/district/upload')]
class ImportDistrictController extends AbstractController
{
    public function __construct(
        private readonly CacheDistrictService $cacheDistrictService,
        private readonly UserActionLogger $userActionLogger,
    )
    {
    }

    #[Route('/', name: 'app_district_upload', methods: ['GET', 'POST'])]
    public function upload(Request $request, DistrictExcelImporter $importer): Response
    {
        if (
            $request->isMethod('POST')
            && $this->isCsrfTokenValid('districtImported', $request->getPayload()->getString('_districtCsrfToken'))
        ){
            $file = $request->files->get('district_file'); //dd($file);

            if (!$file || $file->getClientOriginalExtension() !== 'xlsx') {
                sweetalert()->error("Echèc, Veuillez uploader un fichierExcel (.xlsx)");
                return $this->redirectToRoute('app_district_upload');
            }

            try {
                $result = $importer->import($file);
                sweetalert()->success("Importation terminée: {$result['imported']} districts ajoutés, {$result['skipped']} ignorés");

                if (!empty($result['errors'])) {
                    foreach ($result['errors'] as $error) {
                        sweetalert()->info($error);
                    }
                }

                $this->userActionLogger->log("Importation District",[
                    'action' => "Importation terminée: {$result['imported']} districts ajoutés, {$result['skipped']} ignorés"
                ]);

                // Suppression du cache
                $this->cacheDistrictService->clearAllDistrictCache();

                return $this->redirectToRoute('app_district_list');
            } catch (\Throwable $e){
                sweetalert()->error("Erreur d'importation : {$e->getMessage()}");
            }
        }
        return $this->render('district/upload.html.twig');
    }
}
