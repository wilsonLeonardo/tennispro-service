<?php

namespace App\Services;

use App\Model\User;
use App\Model\Club;
use App\Model\ClubUser;
use Illuminate\Support\Facades\DB;

class ClubService
{
    // public static function create($data) {
    //     $user = new User();


    //     $user->fill($data);
    //     $user->username = $data['name'];
    //     $user->profile = $data['profile'];
    //     $user->remember_token = str_random(10);
    //     $user->status = 'ACTIVE';

    //     $user->save();

    //     return $user;
    // }
    public static function createClub($data) {

        $club = new Club();
        $club->fill($data);

        $club->save();

        return $club->id;
    }
    public static function createUser($data, $clubId) {
        return DB::transaction(function() use ($data, $clubId) {
            $user = new User();
            $club = new ClubUser();
    
            $user->fill($data);
            $user->username = $data['name'];
            $user->profile = $data['profile'];
            $user->remember_token = str_random(10);
            $user->status = 'ACTIVE';
            $user->save();
            
            $club->club()->associate(Club::findOrfail($clubId));
            $club->user()->associate(User::findOrfail($user->id));
            $club->save();

            return $user;
        });
    }
    public static function find() {
        return Club::all();
    }
    public static function updateUser($data) {
        $user = User::with('clubs')->findOrFail($data['id']);
        $user->name = $data['name'];
        $clubId = $user->clubs->toArray()[0]['club_id'];
        $user->save();

        return $clubId;
    }

    public static function update($data) {
        $club = Club::findOrFail($data['id']);
        $club->fill($data);

        return $club->save();
    }

}