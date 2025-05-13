<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\UserActionLogger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/user-logs')]
class UserLogsController extends AbstractController
{
    public function __construct(private readonly UserActionLogger $userActionLogger)
    {
    }

    #[Route('/', name: 'app_admin_user_logs', methods: ['GET'])]
    public function show(): Response
    {
        $logFile = $this->getParameter('kernel.logs_dir').'/user_actions.log';
        $logs = [];

        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $logs[] = $this->parseLogLine($line);
            }
            $logs = array_reverse($logs); // Pour afficher de facon decroissante
        }

        $this->userActionLogger->log("Consultation des logs", ['action'=> " a consulté le tableau des logs."]);

        return $this->render('logs/show.html.twig', [
            'logs' => $logs
        ]);
    }

    private function parseLogLine(mixed $line): array
    {
        $pattern = '/\[(?P<date>.*?)\] (?P<channel>.*?).(?P<level>.*?): (?P<message>.*?) (?P<context>\{.*\})/';
        preg_match($pattern, $line, $matches);

        $context = json_decode($matches['context'] ?? '{}', true) ?? [];

        return [
            'date' => $matches['datetime'] ?? '',
            'level' => $matches['level'] ?? '',
            'message' => $matches['message'] ?? '',
            'context' => $context,
        ];
    }
}
