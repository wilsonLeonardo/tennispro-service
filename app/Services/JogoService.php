<?php

namespace App\Services;

use App\Model\Jogo;
use App\Model\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JogoService
{
    public static function create($ownerId,$data) {

        $foreignId = $data['foreign_id'];
        //var_dump($data['dia']);
        $time = Carbon::createFromFormat('d-m-Y', $data['dia'])->toDateString();
        $data['dia'] = $time;
        return DB::transaction(function() use ($ownerId, $foreignId, $data) {
            $jogo = new Jogo();

            $jogo->fill($data);
            $jogo->owner()->associate(User::findOrfail($ownerId));
            $jogo->foreign()->associate(User::findOrfail($foreignId));
            $jogo->status = 'PENDING';     

            $jogo->save();

            return $jogo;
        });
    }
    public static function index($userId)
    {
        $query = "CAST(dia AS DATE) ASC";
        $games = Jogo::query()
                        ->with('owner')
                        ->with('foreign')
                        ->where(function ($query) use($userId){
                            $query->where('owner_id','=', $userId)
                                ->orWhere('foreign_id', '=', $userId);
                        })->where('status', '=', 'ACCEPTED')
                        ->orderByRaw($query)->get();

                        // const me = id == game.owner.id ? game.owner.name : game.foreign.name;
                        // const adv = id == game.owner.id ? game.foreign.name : game.owner.name;
                        //this.state.status && this.state.gameId == game.id? 


        return collect($games->toArray())->map(function($game) {
            return [
                'id' => $game['id'],
                'owner_id' => $game['owner']['id'],
                'foreign_id' => $game['foreign']['id'],
                'image_owner' => $game['owner']['image'] ? url('storage/users/'.$game['owner']['image']): null,
                'image_foreign' => $game['foreign']['image'] ? url('storage/users/'.$game['foreign']['image']): null,
                'owner_name' => $game['owner']['name'],
                'foreign_name' => $game['foreign']['name'],
            ];
        })->toArray();

       // return response()->json($games);
        
    }
    
    public static function pendingGames($userId)
    {
        
        $games = Jogo::query()
                        ->with('owner')
                        ->with('foreign')
                        ->where(function ($query) use($userId){
                            $query->where('owner_id','=', $userId)
                                ->orWhere('foreign_id', '=', $userId);
                        })->where('status', '=', 'PENDING')->get();

        return collect($games->toArray())->map(function($game) {
            return [
                'id' => $game['id'],
                'owner_id' => $game['owner']['id'],
                'foreign_id' => $game['foreign']['id'],
                'image_owner' => $game['owner']['image'] ? url('storage/users/'.$game['owner']['image']): null,
                'image_foreign' => $game['foreign']['image'] ? url('storage/users/'.$game['foreign']['image']): null,
                'owner_name' => $game['owner']['name'],
                'foreign_name' => $game['foreign']['name'],
            ];
        })->toArray();
        
    }
    public static function publish($jogoId)
    {
        $jogo = Jogo::findOrFail($jogoId);

        $jogo->status = 'ACCEPTED';

        $jogo->save();
    }

    public static function win($jogoId, $userId)
    {
        $jogo = Jogo::findOrFail($jogoId);

        if($userId === $jogo['owner_id'])
            $jogo->setOwnerWin();
        else   
            $jogo->setForeignWin();

        $jogo->status = 'DONE';

        $jogo->save();
    }
    public static function lose($jogoId, $userId)
    {
        $jogo = Jogo::findOrFail($jogoId);

        if($userId === $jogo['owner_id'])
            $jogo->setForeignWin();
        else   
            $jogo->setOwnerWin();

        $jogo->status = 'DONE';

        $jogo->save();
    }

    public static function reject($jogoId)
    {
        $jogo = Jogo::findOrFail($jogoId);

        $jogo->status = 'REJECTED';

        $jogo->save();
    }
    public static function countGames($userId)
    {
        $games = Jogo::query()
                        ->where(function ($query) use($userId){
                            $query->where('owner_id','=', $userId)
                                ->orWhere('foreign_id', '=', $userId);
                        })->where('status', '=', 'DONE')->count();
        return $games;
    }
    public static function countWin($userId)
    {

        $games = Jogo::query()
                    ->where(function ($query) use($userId){
                        $query->where('foreign_id','=', $userId)
                            ->where('winner', '=', 'FOREIGN');
                    })
                    ->orWhere(function ($query) use($userId){
                        $query->where('owner_id','=', $userId)
                            ->where('winner', '=', 'OWNER');
                    })
                    ->where('status', '=', 'DONE')->count();

        return $games;
    }
    public static function countLose($userId)
    {

        $games = Jogo::query()
                    ->where(function ($query) use($userId){
                        $query->where('foreign_id','=', $userId)
                            ->where('winner', '=', 'OWNER');
                    })
                    ->orWhere(function ($query) use($userId){
                        $query->where('owner_id','=', $userId)
                            ->where('winner', '=', 'FOREIGN');
                    })
                    ->where('status', '=', 'DONE')->count();

        return $games;
    }
    
}