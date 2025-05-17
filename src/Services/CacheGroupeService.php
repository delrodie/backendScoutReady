<?php

namespace App\Services;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CacheGroupeService
{
    public function __construct(
        private readonly CacheInterface $groupeCache,
        private readonly ApiKeyService $apiKeyService
    )
    {
    }

    public function getAllGroupe()
    {
        return $this->groupeCache->get('groupe_all', function (ItemInterface $item){
            $item->expiresAfter(604800);
            return $this->apiKeyService->fetchData('/api/groupes');
        });
    }

    public function getGroupeById($id)
    {
        return $this->groupeCache->get('groupe_'.$id, function (ItemInterface $item) use($id){
            $item->expiresAfter(604800);
            return $this->apiKeyService->fetchData('/api/groupes/'.$id);
        });
    }

    public function clearCacheGroupe(int $id = null): bool
    {
        if ($id){
            return $this->groupeCache->delete('groupe_'.$id);
        }
        return $this->groupeCache->delete('groupe_all');
    }
}