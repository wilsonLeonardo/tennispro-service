<?php

namespace App\Http\Controllers;
use App\Services\JogoService;

use Illuminate\Http\Request;

class JogoController extends Controller
{
    public function store(Request $request){
        return JogoService::create(auth()->user()->getAuthIdentifier(), $request->all());
    }
    public function accept($id){
        return JogoService::publish($id);
    }
    public function reject($id){
        return JogoService::reject($id);
    }

    public function getMyGames(){
        return JogoService::index(auth()->user()->getAuthIdentifier());
    }
    public function getPendingGames(){
        return JogoService::pendingGames(auth()->user()->getAuthIdentifier());
    }
    public function setWin($id){
        return JogoService::win($id, auth()->user()->getAuthIdentifier());
    }
    public function setLose($id){
        return JogoService::lose($id, auth()->user()->getAuthIdentifier());
    }
}
