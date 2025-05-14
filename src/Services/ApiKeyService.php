<?php

namespace App\Services;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ApiKeyService
{
    private string $secret;
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->secret = $parameterBag->get('app.app_apikey_crypto');
    }

    public function encrypt(string $data): string
    {
        $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $this->secret, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    public function decrypt(string $encrypted): false|string
    {
        $data = base64_decode($encrypted);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $ivLength);
        $cirpherText = substr($data, $ivLength);
        return openssl_decrypt($cirpherText, 'aes-256-cbc', $this->secret, 0, $iv);
    }
}