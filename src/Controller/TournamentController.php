<?php


namespace App\Controller;

use App\Service\TournamentService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TournamentController extends ControllerAbstract
{
    /**
     * @Route("/tournament/start", name="game_start", methods={"GET", "POST"})
     */
    public function start(Request $request, TournamentService $tournamentService)
    {
        // Get current user
        $user = $this->getUser();

        // Get strategies IDs from request body
        $strategiesIds = $request->request->get('strategiesIds', []);
        if (!empty($stretegiesIDs)) {
            $strategiesIds = json_decode($stretegiesIDs, true);
        }

        // Start tournament with all selected (or just all enabled user strategies) and get results
        $results = $tournamentService->runTournament($user, $strategiesIds);

        dd($results);
    }
}