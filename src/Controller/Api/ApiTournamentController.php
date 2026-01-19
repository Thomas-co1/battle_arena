<?php

namespace App\Controller\Api;

use App\Entity\MatchResult;
use App\Entity\MatchResultType;
use App\Entity\Tournament;
use App\Repository\MatchResultRepository;
use App\Repository\TournamentRepository;
use App\Service\MatchService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/tournament')]
class ApiTournamentController extends AbstractController
{
    #[Route('/{id}/matches', name: 'api_tournament_matches', methods: ['GET'])]
    public function getMatches(Tournament $tournament, SerializerInterface $serializer): JsonResponse
    {
        $matches = $tournament->getMatches();

        $data = array_map(function (MatchResult $match) {
            return [
                'id' => $match->getId(),
                'player1' => [
                    'id' => $match->getPlayer1()->getId(),
                    'gamertag' => $match->getPlayer1()->getGamertag(),
                    'character' => $match->getPlayer1()->getMainCharacter(),
                ],
                'player2' => [
                    'id' => $match->getPlayer2()->getId(),
                    'gamertag' => $match->getPlayer2()->getGamertag(),
                    'character' => $match->getPlayer2()->getMainCharacter(),
                ],
                'status' => $match->getStatus()->value,
                'scheduledDate' => $match->getScheduledDate()?->format('Y-m-d H:i:s'),
                'playedDate' => $match->getPlayedDate()?->format('Y-m-d H:i:s'),
                'player1_score' => $match->getPlayer1Score(),
                'player2_score' => $match->getPlayer2Score(),
                'player1_result' => $match->getPlayer1Result()?->value,
                'player2_result' => $match->getPlayer2Result()?->value,
                'needs_moderation' => $match->isNeedsModeration(),
            ];
        }, $matches->toArray());

        return new JsonResponse($data);
    }

    #[Route('/{id}/match/{matchId}/submit-result', name: 'api_submit_match_result', methods: ['POST'])]
    public function submitMatchResult(
        int $id,
        int $matchId,
        Request $request,
        TournamentRepository $tournamentRepository,
        MatchResultRepository $matchRepository,
        MatchService $matchService
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $tournament = $tournamentRepository->find($id);
        if (!$tournament) {
            return new JsonResponse(['error' => 'Tournament not found'], Response::HTTP_NOT_FOUND);
        }

        $match = $matchRepository->find($matchId);
        if (!$match || $match->getTournament() !== $tournament) {
            return new JsonResponse(['error' => 'Match not found'], Response::HTTP_NOT_FOUND);
        }

        $player = $this->getUser()->getPlayer();
        if ($match->getPlayer1() !== $player && $match->getPlayer2() !== $player) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        try {
            $data = json_decode($request->getContent(), true);
            $result = MatchResultType::from($data['result']);

            $matchService->submitResult($match, $player, $result);

            return new JsonResponse([
                'success' => true,
                'message' => 'Result submitted',
                'match' => [
                    'id' => $match->getId(),
                    'status' => $match->getStatus()->value,
                    'needs_moderation' => $match->isNeedsModeration(),
                ],
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}/standings', name: 'api_tournament_standings', methods: ['GET'])]
    public function getStandings(Tournament $tournament): JsonResponse
    {
        $players = [];
        $playerMap = [];

        foreach ($tournament->getMatches() as $match) {
            if ($match->isFinished()) {
                $p1 = $match->getPlayer1();
                $p2 = $match->getPlayer2();

                if (!isset($playerMap[$p1->getId()])) {
                    $playerMap[$p1->getId()] = [
                        'id' => $p1->getId(),
                        'gamertag' => $p1->getGamertag(),
                        'character' => $p1->getMainCharacter(),
                        'wins' => 0,
                        'losses' => 0,
                    ];
                }

                if (!isset($playerMap[$p2->getId()])) {
                    $playerMap[$p2->getId()] = [
                        'id' => $p2->getId(),
                        'gamertag' => $p2->getGamertag(),
                        'character' => $p2->getMainCharacter(),
                        'wins' => 0,
                        'losses' => 0,
                    ];
                }

                if ($match->getPlayer1Result()->value === 'win') {
                    $playerMap[$p1->getId()]['wins']++;
                    $playerMap[$p2->getId()]['losses']++;
                } else {
                    $playerMap[$p2->getId()]['wins']++;
                    $playerMap[$p1->getId()]['losses']++;
                }
            }
        }

        $players = array_values($playerMap);
        usort($players, fn ($a, $b) => $b['wins'] <=> $a['wins']);

        return new JsonResponse($players);
    }
}
