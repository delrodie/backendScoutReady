<?php

namespace App\Services\Import;

use App\Services\ApiKeyService;
use App\Services\Cache\CacheDistrictService;
use App\Services\UserActionLogger;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class GroupeExcelImporter
{
    public function __construct(
        private readonly CacheDistrictService $cacheDistrictService,
        private readonly ApiKeyService $apiKeyService,
        private UserActionLogger $userActionLogger
    )
    {
    }

    public function import(UploadedFile $file)
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Recuepration de la lise des districts
        $districts = $this->cacheDistrictService->getAllDistrict();
        $districtMap = [];
        foreach ($districts as $district) {
            $key = mb_strtolower(trim($district['nom']));
            $districtMap[$key] = $district['id'];
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;

            [$districtInput, $groupeNom] = $row;

            // Si le nom du groupe est vide
            if (empty($groupeNom)){
                $skipped++;
                continue;
            }

            $districtId = null;

            // Verifions si la colonne contient un ID ou un nom
            // Si la colonne contient un nom alors rechercher le district dans le cache
            if (is_numeric($districtInput)){
                $districtId = (int) $districtInput;
            }else{
                $districtKey = mb_strtolower(trim($districtInput));
                if (isset($districtMap[$districtKey])){
                    $districtId = $districtMap[$districtKey];
                } else{
                    $skipped++;
                    $errors[] = "Ligne ".($index+1)." : district '{$districtInput}' introuvable.";
                    $this->userActionLogger->log("Echec d'importation du district",[
                        'action' => "Le district '{$districtInput}' de la ligne ".($index+1)." est introuvable."
                    ]);
                    continue;
                }
            }

            try {
                $this->apiKeyService->sendData('/api/groupes',[
                    'paroisse' => trim($groupeNom),
                    'district' => $districtId
                ]);
                $imported++;
            }catch (\Throwable $e){
                $skipped++;
                $errors[] = "Ligne ".($index+1).": erreur d'envoi API - ".$e->getMessage();
                $this->userActionLogger->log("Echec d'importation du district",[
                    'action' => "Ligne ".($index+1).": erreur d'envoi API - ".$e->getMessage()
                ]);
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors ? [ 1 => "Veuillez voir les logs pour les différentes erreurs" ]: [],
//            'errors' => $errors,
        ];

    }
}