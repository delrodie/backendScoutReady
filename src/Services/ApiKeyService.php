<?php

namespace App\Services;

use App\Repository\ApiCredentialRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiKeyService
{
    private string $secret;
    public function __construct(
        ParameterBagInterface $parameterBag,
        private HttpClientInterface $httpClient,
        private ApiCredentialRepository $apiCredentialRepository
    )
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


    public function fetchData(?string $ressource)
    {
        $apiKey = $this->apiCredentialRepository->findOneBy([], ['id' => 'DESC']); //dd($apiKey);
        if (!$apiKey) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "Clé API introuvable");
        }

        try {
            $response = $this->httpClient->request(
                'GET',
                $apiKey->getUrl() . $ressource,
                [
                    'headers' => [
                        'x-api-key' => $this->decrypt($apiKey->getApikey()),
                        'Content-Type' => 'application/json',
                    ],
                ]
            );

            $statusCode = $response->getStatusCode();
            if ($statusCode !== Response::HTTP_OK) {
                // Récupérer la réponse brute
                $rawContent = $response->getContent(false);
                $decodedContent = json_decode($rawContent, true);

                // Vérifier si la réponse JSON est bien décodée
                if (json_last_error() !== JSON_ERROR_NONE || !isset($decodedContent['detail'])) {
                    throw new HttpException($statusCode, "Erreur API : réponse invalide.");
                }

                // Lever l'exception avec le message renvoyé par l'API
                throw new HttpException($statusCode, $decodedContent['detail']);
            }

            return $response->toArray();

        }catch (HttpException $e) {
            // Laisser Symfony gérer cette exception sans la masquer
            throw $e;
        } catch (\Throwable $e) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, "Erreur lors de la récupération des données : " . $e->getMessage());
        }
    }

    public function sendData(string $url, array $data, string $method = 'POST', array $headers = [], array $options = []): array
    {
        $apiKey = $this->apiCredentialRepository->findOneBy(['status' => true]);
        if (!$apiKey){
            throw new HttpException(Response::HTTP_NOT_FOUND, "Clé API introuvable");
        }

        try{
            // Les entêtes par defaut
            $defaultHeaders = [
                'x-api-key' => $this->decrypt($apiKey->getApikey()),
                'Content-Type' => 'application/json'
            ];
            $headers = array_merge($defaultHeaders, $headers);

            // Envoie de la requête HTTP avec HttpClient
            $response = $this->httpClient->request(
                $method,
                $apiKey->getUrl() . $url,
                [
                    'headers' => $headers,
                    'json' => $data,
                ]
            );

            // Verifie du statut HTTP
            $statusCode = $response->getStatusCode();
            if ($statusCode !== Response::HTTP_OK && $statusCode !== Response::HTTP_CREATED) {
                $rawContent = $response->getContent(false);
                $decodedContent = json_decode($rawContent, true);

                if (json_last_error() !== JSON_ERROR_NONE || !isset($decodedContent['detail'])){
                    throw new HttpException($statusCode, "Erreur API : réponse invalide.");
                }

                throw new HttpException($statusCode, $decodedContent['detail']);
            }

            return $response->toArray();
        } catch(HttpException $e){
            throw $e;
        } catch (\Throwable $e){
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, "Erreur lors de l'envoi des données : {$e->getMessage()}");
        }
    }

}