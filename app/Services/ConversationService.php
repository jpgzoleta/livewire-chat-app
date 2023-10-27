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

    public static function getUserConversations()
    {
        $conversations = DB::select('EXEC sp_Conversations_Get_JSON ?', [auth()->user()->id]);
        $decodedConversations = [];
        if ($conversations) {
            foreach (get_object_vars($conversations[0]) as $var => $val) {
                foreach (json_decode($val) as $v) {
                    $decodedConversations[] = $v;
                }
                ;
            }
        }
        return $decodedConversations;
    }
}