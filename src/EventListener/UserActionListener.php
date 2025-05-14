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

            default:
                break;
        }
    }
}
