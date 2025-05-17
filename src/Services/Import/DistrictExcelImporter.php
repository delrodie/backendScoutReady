<?php

namespace App\Services\Import;

use App\Services\ApiKeyService;
use App\Services\CacheRegionService;
use App\Services\UserActionLogger;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DistrictExcelImporter
{
    public function __construct(
        private readonly CacheRegionService $cacheRegionService,
        private readonly ApiKeyService      $apiKeyService,
        private readonly UserActionLogger $userActionLogger,
    )
    {
    }

    public function import(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Recuperation de la liste des regions mise en cache
        $regions = $this->cacheRegionService->getAllRegion();
        $regionMap = [];
        foreach ($regions as $region) {
            $key = mb_strtolower(trim($region['nom']));
            $regionMap[$key] = $region['id'];
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;

            [$regionInput, $districtNom] = $row;

            // Si le nom de district est vide
            if (empty($districtNom)){
                $skipped++;
                continue;
            }

            $regionId = null;

            // Verifions si la colonne contient un ID ou un nom
            // Si la colonne contient un nom alors rechercher la region dans le cache
            if (is_numeric($regionInput)){
                $regionId = (int) $regionInput;
            }else{
                $regionKey = mb_strtolower(trim($regionInput));
                if (isset($regionMap[$regionKey])){
                    $regionId = $regionMap[$regionKey];
                } else{
                    $skipped++;
                    $errors[] = "Ligne ".($index+1)." : région '{$regionInput}' introuvable.";
                    $this->userActionLogger->log("Echec d'importation du district",[
                        'action' => "La region '{$regionInput}' de la ligne ".($index+1)." est introuvable."
                    ]);
                    continue;
                }
            }

            try {
                $this->apiKeyService->sendData('/api/districts',[
                    'nom' => trim($districtNom),
                    'region' => $regionId
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
            'errors' => $errors
        ];
    }
}