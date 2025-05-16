<?php

namespace App\EventListener;

use App\Services\UserActionLogger;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

final class UserActionListener
{

    public function __construct(private readonly UserActionLogger $userActionLogger)
    {
    }

    #[AsEventListener]
    public function onTerminateEvent(TerminateEvent $event): void
    {
        $routeName = $event->getRequest()->attributes->get('_route');
        $method = $event->getRequest()->getMethod() ?? null;

        $this->logAction($routeName, $method, $event);
    }

    private function logAction(?string $routeName, ?string $method, $event): void
    {
        if (!$routeName) {
            return;
        }

        $request = $event->getRequest();

        switch ($routeName) {
            case 'app_dashboard':
                $this->userActionLogger->log("Consultation du dashboard", ['action' => ' a consulté le dashboard']);
                break;
            case 'app_admin_user_index':
                $this->userActionLogger->log("Consultation des utilisateurs", ['action' => ' a consulté la liste des utilisateurs']);
                break;
            case 'app_admin_user_new':
                if ($method === 'POST'){
                    $postData = $event->getRequest()->request->all();
                    $this->userActionLogger->log("Enregistrement d'utilisateur",['action' => " a enregistré l'utilisateur " . $postData['user_form']['email']]);
                }
                break;
            case 'app_admin_user_edit':
                if ($method === 'POST'){
                    $postData = $event->getRequest()->request->all();
                    $this->userActionLogger->log("Modification d'utilisateur",['action' => " a modifié l'utilisateur " . $postData['user_form']['email']]);
                }
                break;
            case 'app_api_credential_show':
                $this->userActionLogger->log("Consultation de la clé API", ['action' => ' a consulté les détails de la clé de l\'API']);
                break;
            case 'app_api_credential_new':
                if ($method === 'POST'){
                    $postData = $event->getRequest()->request->all();
                    $this->userActionLogger->log("Enregistrement de ApiKey",['action' => " a enregistré la clé de l'API " . $postData['api_credential_form']['url']]);
                }
                break;
            case 'app_api_credential_edit':
                if ($method === 'POST'){
                    $postData = $event->getRequest()->request->all();
                    $this->userActionLogger->log("Modification de l'ApiKey",['action' => " a modifié l'ApiKey " . $postData['api_credential_form']['url']]);
                }
                break;

            case 'app_asn_list':
                $this->userActionLogger->log("Consultation liste ASNs", ['action' => " a consulté la liste des ASNs"]);
                break;

            case 'app_asn_new':
                if ($method === 'POST') {
                    $sigle = $request->get('asn_sigle') ?? 'N/A';
                    $nom = $request->get('asn_nom') ?? 'N/A';
                    $this->userActionLogger->log("Enregistrement de ASN", [
                        'action' => " a enregistré l'ASN « $sigle :: $nom »"
                    ]);
                }
                break;

            case 'app_asn_edit':
                if ($method === 'POST') {
                    $sigle = $request->get('asn_sigle') ?? 'N/A';
                    $nom = $request->get('asn_nom') ?? 'N/A';
                    $this->userActionLogger->log("Modification de ASN", [
                        'action' => " a modifié l'ASN « $sigle :: $nom » "
                    ]);
                }
                break;

            case 'app_asn_delete':
                if ($method === 'POST') {
                    $sigle = $request->get('asn_sigle') ?? 'N/A';
                    $nom = $request->get('asn_nom') ?? 'N/A';
                    $this->userActionLogger->log("Suppression de ASN", [
                        'action' => " a supprimé l'ASN « $sigle :: $nom » "
                    ]);
                }
                break;

            case 'app_cache_delete_all':
                $this->userActionLogger->log("Suppression du cache", ['action' => " a supprimé tous les caches."]);
                break;

            case 'app_cache_delete_module' :
                if ($method === 'GET') {
                    $module = strtoupper($request->get('module'));
                    $this->userActionLogger->log("Suppression de cache",['action' => " a supprimer le cache $module"]);
                }
                break;

            case 'app_region_list':
                $this->userActionLogger->log("Consultation liste Regions", ['action' => " a consulté la liste des régions"]);
                break;

            case 'app_region_new':
                if ($method === 'POST') {
                    $nom = $request->get('region_nom') ?? 'N/A';
                    $asn = $request->get('region_asn') ?? 'N/A';
                    $this->userActionLogger->log("Enregistrement de Region", [
                        'action' => " a enregistré la région « $asn :: $nom »"
                    ]);
                }
                break;

            case 'app_region_update':
                if ($method === 'POST') {
                    $asn = $request->get('region_asn') ?? 'N/A';
                    $nom = $request->get('region_nom') ?? 'N/A';
                    $this->userActionLogger->log("Modification de Region", [
                        'action' => " a modifié la région « $asn :: $nom » "
                    ]);
                }
                break;

            case 'app_region_delete':
                if ($method === 'POST') {
                    $asn = $request->get('region_asn') ?? 'N/A';
                    $nom = $request->get('region_nom') ?? 'N/A';
                    $this->userActionLogger->log("Suppression de Region", [
                        'action' => " a supprimé la région « $asn :: $nom » "
                    ]);
                }
                break;

            default:
                break;
        }
    }
}
