<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Campeonato;
use App\Model\User;
use App\Model\CampeonatoInscrito;
use Illuminate\Support\Facades\Log;

class CampeonatoController extends Controller
{
    public function store(Request $request){
        $camp = new Campeonato ();
        $camp->fill($request->all());
        $camp->niveis = implode(", ", $request->all()['niveis']);
        $camp->status = 'PROGRESS';
        $camp->user()->associate(User::findOrFail(auth()->user()->getAuthIdentifier()));

        return response()->json($camp->save());
    }
    public function subscribe($id){
        $camp = CampeonatoInscrito::query()->where('camp_id', $id)->where('user_id', auth()->user()->getAuthIdentifier())->first();

        if(!isset($camp)){
            $subscribe = new CampeonatoInscrito;
            $subscribe->user()->associate(User::findOrFail(auth()->user()->getAuthIdentifier()));
            $subscribe->camp()->associate(Campeonato::findOrFail($id));
    
            return response()->json($subscribe->save());
        }
           return response()->json(['error' => 'Você já esta cadastrado nesse campeonato!'], 500);     
        
            
    }
    public function done($id){
        $camp = Campeonato::findOrFail($id);
        $camp->status = 'DONE';

        return response()->json($camp->save());
    }
    public function progressCamp(){
        
        $camp = Campeonato::query()->where('user_id', auth()->user()->getAuthIdentifier())->where('status', '=', 'PROGRESS')->get();

        return response()->json($camp);
    }
    public function doneCamp(){
        
        $camp = Campeonato::query()->where('user_id', auth()->user()->getAuthIdentifier())->where('status', '=', 'DONE')->get();

        return response()->json($camp);
    }

}
