<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/queue-status', name: 'admin_queue_status')]
class QueueStatusController extends AbstractController
{
    public function __construct(private readonly Connection $connection)
    {
    }

    #[Route('', name: '', methods: ['GET'])]
    public function index(): Response
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT id, queue_name, body, created_at, available_at, delivered_at'
            . ' FROM messenger_messages ORDER BY created_at DESC'
        );

        $messages = array_map(fn(array $row) => [
            'id'           => $row['id'],
            'queue_name'   => $row['queue_name'],
            'created_at'   => $row['created_at'],
            'delivered_at' => $row['delivered_at'],
            'status'       => $this->resolveStatus($row),
            'images'       => $this->extractImages($row['body']),
            'error'        => $this->extractError($row['body']),
        ], $rows);

        return $this->render('admin/queue_status.html.twig', ['messages' => $messages]);
    }

    #[Route('/{id}/retry', name: '_retry', methods: ['POST'])]
    public function retry(int $id): RedirectResponse
    {
        $this->connection->executeStatement(
            "UPDATE messenger_messages SET queue_name = 'default', delivered_at = NULL, available_at = NOW()"
            . " WHERE id = ? AND queue_name = 'failed'",
            [$id]
        );

        $this->addFlash('success', 'Message requeued.');

        return $this->redirectToRoute('admin_queue_status');
    }

    #[Route('/{id}/delete', name: '_delete', methods: ['POST'])]
    public function delete(int $id): RedirectResponse
    {
        $this->connection->executeStatement(
            "DELETE FROM messenger_messages WHERE id = ? AND queue_name = 'failed'",
            [$id]
        );

        $this->addFlash('success', 'Message deleted.');

        return $this->redirectToRoute('admin_queue_status');
    }

    private function resolveStatus(array $row): string
    {
        if ($row['queue_name'] === 'failed') {
            return 'failed';
        }
        return $row['delivered_at'] ? 'processing' : 'waiting';
    }

    /** @return string[] */
    private function extractImages(string $body): array
    {
        // Extract string values from PHP-serialized imageS3Keys array
        preg_match_all('/s:\d+:"([^"]+\.(?:jpg|jpeg|png|gif|webp))"/i', $body, $matches);
        return $matches[1] ?? [];
    }

    private function extractError(string $body): ?string
    {
        // RedeliveryStamp stores the exception message in a serialized string property
        if (preg_match('/RedeliveryStamp[^;]+s:\d+:"([^"]{10,})"/s', $body, $m)) {
            return $m[1];
        }
        // Fallback: look for exception message pattern
        if (preg_match('/exceptionMessage[^;]*s:\d+:"([^"]{10,})"/s', $body, $m)) {
            return $m[1];
        }
        return null;
    }
}
