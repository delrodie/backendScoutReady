<?php

namespace App\Services;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CacheAsnService
{
    public function __construct(
        private  CacheInterface $asnCache,
        private  ApiKeyService $apiKeyService,
    )
    {
    }

    public function getAllAsns(): array
    {
        return $this->asnCache->get('asn_all', function (ItemInterface $item){
            $item->expiresAfter(604800);
            return $this->apiKeyService->fetchData('/api/asns');
        });
    }

    public function getAsnById(int $id)
    {
        return $this->asnCache->get('asn_'.$id, function(ItemInterface $item) use($id) {
            $item->expiresAfter(604800);
            return $this->apiKeyService->fetchData('/api/asns/'.$id);
        });
    }

    public function invalidateAsnCache(): void
    {
        $this->asnCache->delete('asn_all');
    }

    public function invalidateAsnById(int $id): void
    {
        $this->asnCache->delete('asn_'.$id);
    }
}