<?php

namespace App\Services\Cache;

use App\Services\ApiKeyService;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CacheDistrictService
{
    public function __construct(
        private CacheInterface $districtCache,
        private readonly ApiKeyService $apiKeyService
    )
    {
    }

    public function getAllDistrict()
    {
        return $this->districtCache->get('district_all', function (ItemInterface $item) {
            $item->expiresAfter(604800);
            return $this->apiKeyService->fetchData('/api/districts');
        });
    }

    public function getDistrictById($id)
    {
        return $this->districtCache->get('district_'.$id, function (ItemInterface $item) use($id){
            $item->expiresAfter(604800);
            return $this->apiKeyService->fetchData('/api/districts/'.$id);
        });
    }

    public function clearAllDistrictCache()
    {
        return $this->districtCache->delete('district_all');
    }

    public function clearDistrictCacheById($id)
    {
        return $this->districtCache->delete('district_'.$id);
    }
}