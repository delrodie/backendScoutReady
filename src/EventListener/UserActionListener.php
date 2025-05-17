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

        if ($routeName) {
            $this->logAction($routeName, $method, $event);
        }
    }

    private function logAction(string $routeName, ?string $method, TerminateEvent $event): void
    {
        $request = $event->getRequest();

        $actionMap = [
            'app_dashboard' => "Consultation du dashboard",
            'app_admin_user_index' => "Consultation des utilisateurs",
            'app_api_credential_show' => "Consultation de la clé API",
            'app_asn_list' => "Consultation liste ASNs",
            'app_cache_delete_all' => "Suppression du cache",
            'app_region_list' => "Consultation liste Regions",
            'app_district_list' => "Consultation liste Districts",
        ];

        $dynamicActions = [
            'app_admin_user_new' => fn() => ["Enregistrement d'utilisateur", ['action' => " a enregistré l'utilisateur " . $request->request->get('user_form')['email']]],
            'app_admin_user_edit' => fn() => ["Modification d'utilisateur", ['action' => " a modifié l'utilisateur " . $request->request->get('user_form')['email']]],
            'app_api_credential_new' => fn() => ["Enregistrement de ApiKey", ['action' => " a enregistré la clé de l'API " . $request->request->get('api_credential_form')['url']]],
            'app_api_credential_edit' => fn() => ["Modification de l'ApiKey", ['action' => " a modifié l'ApiKey " . $request->request->get('api_credential_form')['url']]],
            'app_asn_new' => fn() => ["Enregistrement de ASN", ['action' => " a enregistré l'ASN « {$request->get('asn_sigle')} :: {$request->get('asn_nom')} »"]],
            'app_asn_edit' => fn() => ["Modification de ASN", ['action' => " a modifié l'ASN « {$request->get('asn_sigle')} :: {$request->get('asn_nom')} »"]],
            'app_asn_delete' => fn() => ["Suppression de ASN", ['action' => " a supprimé l'ASN « {$request->get('asn_sigle')} :: {$request->get('asn_nom')} »"]],
            'app_cache_delete_module' => fn() => ["Suppression de cache", ['action' => " a supprimé le cache " . strtoupper($request->get('module'))]],
            'app_region_new' => fn() => ["Enregistrement de Region", ['action' => " a enregistré la région « {$request->get('region_asn')} :: {$request->get('region_nom')} »"]],
            'app_region_update' => fn() => ["Modification de Region", ['action' => " a modifié la région « {$request->get('region_asn')} :: {$request->get('region_nom')} »"]],
            'app_region_delete' => fn() => ["Suppression de Region", ['action' => " a supprimé la région « {$request->get('region_asn')} :: {$request->get('region_nom')} »"]],
            'app_district_new' => fn() => ["Enregistrement de District", ['action' => " a enregistré le district « {$request->get('district_region')} :: {$request->get('district_nom')} »"]],
            'app_district_update' => fn() => ["Modification de District", ['action' => " a modifié le district « {$request->get('district_region')} :: {$request->get('district_nom')} »"]],
            'app_district_delete' => fn() => ["Suppression de District", ['action' => " a supprimé le district « {$request->get('district_region')} :: {$request->get('district_nom')} »"]],
        ];

        if (isset($actionMap[$routeName])) {
            $this->userActionLogger->log($actionMap[$routeName], ['action' => " a exécuté l'action correspondante."]);
        } elseif (isset($dynamicActions[$routeName]) && $method === 'POST') {
            [$logMessage, $logData] = $dynamicActions[$routeName]();
            $this->userActionLogger->log($logMessage, $logData);
        }
    }
}
