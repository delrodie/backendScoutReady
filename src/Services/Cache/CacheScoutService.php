<?php

namespace App\Services\Cache;

use App\Services\ApiKeyService;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CacheScoutService
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly ApiKeyService $apiKeyService,
    )
    {
    }

    public function getAllScoutCache()
    {
        return $this->cache->get('scout_all', function (ItemInterface $item){
            $item->expiresAfter(604800);
            return $this->apiKeyService->fetchData('/api/scouts/');
        });
    }

    public function getScoutCacheById($id)
    {
        return $this->cache->get('scout_'.$id, function (ItemInterface $item) use ($id){
            $item->expiresAfter(604800);
            return $this->apiKeyService->fetchData('/api/scouts/'.$id);
        });
    }

    public function clearScoutCache(?int $id=null): bool
    {
        if ($id) return $this->cache->delete('scout_'.$id);
        return $this->cache->delete('scout_all');
    }
}