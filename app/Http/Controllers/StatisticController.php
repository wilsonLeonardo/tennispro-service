<?php

namespace App\Http\Controllers;
use App\Services\JogoService;

use Illuminate\Http\Request;

class StatisticController extends Controller
{
    public function index(){

        $userId = auth()->user()->getAuthIdentifier();

        $data['games'] = JogoService::countGames($userId);
        $data['win'] = JogoService::countWin($userId);
        $data['lose'] = JogoService::countLose($userId);


        return $data;

    }
}
