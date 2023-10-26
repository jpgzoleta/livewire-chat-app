<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class UserService
{
    public static function getUsers($toSearchString)
    {
        $users = DB::select('EXEC sp_Users_Get ?', [$toSearchString]);

        return $users;
    }
}