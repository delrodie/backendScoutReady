<?php

namespace App\Services;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CacheRegionService
{
    public function __construct(
        private readonly CacheInterface $regionCache,
        private readonly ApiKeyService $apiKeyService,
    )
    {
    }

    public function getAllRegion()
    {
        return $this->regionCache->get('region_all', function(ItemInterface $item){
            $item->expiresAfter(604800);
            return $this->apiKeyService->fetchData('/api/regions');
        });
    }

    public function getRegionById($id)
    {
        return $this->regionCache->get('region_'.$id, function(ItemInterface $item) use($id) {
            $item->expiresAfter(604800);
            return $this->apiKeyService->fetchData('/api/regions/'.$id);
        });
    }

    public function clearAllRegionCache(): bool
    {
        return $this->regionCache->delete('region_all');
    }

    public function clearRegionCacheById($id)
    {
        return $this->regionCache->delete('region_'.$id);
    }
}