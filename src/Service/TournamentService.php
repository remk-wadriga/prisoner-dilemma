<?php


namespace App\Service;

use App\Entity\User;

class TournamentService extends AbstractService
{
    public function getParams(): array
    {
        return [

        ];
    }

    public function start()
    {

    }

    public function runTournament(User $user, $strategiesIds = [])
    {
        dd($strategiesIds);
    }
}