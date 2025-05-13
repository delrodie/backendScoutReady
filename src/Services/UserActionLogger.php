<?php

namespace App\Services;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class UserActionLogger
{
    public const ACTION_CONNEXION = " s'est connecté à la plateforme";
    public const ACTION_CONSUL_LOGS = " a consulté le tableau des logs";
    public const MESSAGE_CONNEXION = "Connexion établie";
    public const MESSAGE_CONSULTATION = "CONSULTATION";

    public function __construct(
        private LoggerInterface $userActionLogger,
        private Security $security,
    )
    {
    }

    public function log(string $message, array $context = []): void
    {
        $user = $this->security->getUser();
        $username = $user ? $user->getUserIdentifier() : 'Anonyme';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'INCONNU';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'INCONNU';
        $deviceType = $this->getDeviceType($userAgent);

        $this->userActionLogger->info($message, array_merge([
            'user' => $username,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'device_type' => $deviceType,
            'datetime' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ], $context));
    }


    private function getLocationFromIp(string $ip): string
    {
        try {
            $response = file_get_contents("https://ip-api.com/json/160.154.255.73?fields=city,country");
            $data = json_decode($response, true);

            if ($data && isset($data['city'], $data['country'])) {
                return $data['city'] . ', ' . $data['country'];
            }
        } catch (\Exception $e) {
            // log error if needed
        }

        return 'INCONNU';
    }

    private function getDeviceType(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);

        if (preg_match('/mobile|android|iphone|ipod|blackberry|windows phone/', $userAgent)) {
            return 'mobile';
        }

        if (preg_match('/tablet|ipad/', $userAgent)) {
            return 'tablette';
        }

        return 'ordinateur';
    }



}