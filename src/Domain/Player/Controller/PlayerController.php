<?php

declare(strict_types=1);

namespace App\Domain\Player\Controller;

use App\Entity\Player;
use App\Entity\PlayerActivity;
use App\Event\PlayerCreated;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class PlayerController extends AbstractController
{
    #[Route('/api/players', name: 'task_create_player', methods: ['POST'], format: 'json')]
    public function __invoke(
        Request $request,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger,
    ): JsonResponse {
        try {
            $payload = $request->toArray();
        } catch (JsonException) {
            return $this->json(['error' => 'The request must contain a valid JSON object.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (!is_string($payload['username'] ?? null) || !is_string($payload['email'] ?? null)) {
            return $this->json(['error' => 'Username and email are required and must be strings.'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $username = trim($payload['username']);
        $email = trim($payload['email']);

        if ($username === '' || mb_strlen($username) > 100) {
            return $this->json(['error' => 'Username must contain between 1 and 100 characters.'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (mb_strlen($email) > 180 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return $this->json(['error' => 'Email must be a valid address of at most 180 characters.'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $player = new Player($username, $email);
            $activity = new PlayerActivity($player, 'player_created');
            $entityManager->persist($player);
            $entityManager->persist($activity);
            $entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->json(['error' => 'A player with this username or email already exists.'], JsonResponse::HTTP_CONFLICT);
        } catch (\Throwable $exception) {
            $logger->error('Player registration failed.', ['exception' => $exception]);

            return $this->json(['error' => 'Unable to create the player.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Dispatch after a successful flush; the email listener handles delivery failures.
        $eventDispatcher->dispatch(new PlayerCreated(
            $player->getId(),
            $player->getUsername(),
            $player->getEmail(),
        ));

        return $this->json([
            'id' => $player->getId(),
            'username' => $player->getUsername(),
            'email' => $player->getEmail(),
        ], JsonResponse::HTTP_CREATED);
    }
}
