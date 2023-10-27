<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class MessageService
{
    public static function createMessage($conversation, $sender, $type, $body)
    {
        $message = DB::select('EXEC sp_Messages_Create ?, ?, ?', [$conversation, $sender, $body]);
        $decoded = [];
        foreach (get_object_vars($message[0]) as $var => $val) {
            foreach (json_decode($val) as $v) {
                $decoded[] = $v;
            }
            ;
        }
        return $decoded[0];
    }

    public static function getConversationMessages($conversationId)
    {
        $messages = DB::select('EXEC sp_Messages_Get_JSON ?', [$conversationId]);
        $decoded = [];
        if ($messages) {
            foreach (get_object_vars($messages[0]) as $var => $val) {
                foreach (json_decode($val) as $v) {
                    $decoded[] = $v;
                }
                ;
            }
        }
        return $decoded;
    }
}