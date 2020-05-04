<?php

namespace App\Services;


use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class FirebaseService
{
    public static function getDatabase()
    {
        $serviceAccount = ServiceAccount::fromJsonFile(config_path() . '/firebase/tennis-pro-d81a1-a3757d50b371.json');
        
        $firebase = (new Factory())
        ->withServiceAccount($serviceAccount)
        ->withDatabaseUri('https://tennis-pro-d81a1.firebaseio.com/')
        ->create();

        return $firebase->getDatabase();
    }

    public static function push($node, $objectToSubmit)
    {
        return self::getDatabase()
            ->getReference($node)
            ->push($objectToSubmit);
    }

    public static function notify($type, $userId)
    {
        return self::getDatabase()
            ->getReference('notifications/' . $userId . '/' . $type)
            ->push(1);
    }

    public static function clearNotification($type, $userId)
    {
        return self::getDatabase()
            ->getReference('notifications/' . $userId . '/' . $type)
            ->remove();
    }

    public static function findByKey($key)
    {
        return self::getDatabase()
            ->getReference($key)
            ->getValue();
    }
}