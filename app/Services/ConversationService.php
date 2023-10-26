<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ConversationService
{
    public static function createPrivateConversation($userToChat)
    {
        $conversation = DB::select('EXEC sp_Conversations_Private_Create ? ?', [auth()->user()->id, $userToChat]);

        return $conversation;
    }
}