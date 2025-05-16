<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;

#[Route('/redis')]
class RedisController extends AbstractController
{
    #[Route('/', name: 'app_config_redis_test')]
    public function test(CacheInterface $cache): Response
    {
        $value = $cache->get('asn_test_cache', function () {
            return '✅ Redis fonctionne avec Symfony !';
        });

//        return new Response("<h2>$value</h2>");
        return $this->render('backend/redis_test.html.twig', [
            'value' => $value,
            'title' => 'Test fonctionnel'
        ]);
    }

    #[Route('/reel', name: 'app_config_redis_reel')]
    public function reel(): Response
    {
        $redis = RedisAdapter::createConnection('redis://localhost');
        $redis->set('test_key', 'Hello Redis');
        $value = $redis->get('test_key');

        return $this->render('backend/redis_test.html.twig', [
            'value' => $value,
            'title' => 'Test réel'
        ]);
    }
}
