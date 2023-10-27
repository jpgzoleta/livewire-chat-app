<?php

namespace App\Http\Controllers;

use App\Services\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    private $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }
    //
    public function store(Request $request)
    {
        $message = $this->messageService->createMessage($request->conversation, $request->sender, 'text', $request->body);

        return response()->json($message);
    }
}
