<?php

namespace App\Http\Controllers;

use App\Http\Requests\Messages\MessageRequest;
use App\Http\Resources\MessageResource;
use App\Library\Results;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function get(Conversation $conversation, Request $request)
    {
        // $this->authorize('view', $conversation);

        $validated = $request->validate([
            'before_id' => ['nullable', 'integer'],
            'after_id' => ['nullable', 'integer']
        ]);

        $messageLimit = 50;
        $query = Message::query()
            ->with([
                'sender',
                'post' => function ($q) {
                    $q->withDetails();
                }
            ])
            ->where('conversation_id', $conversation->id)
            ->orderByDesc('id');
        
        if (isset($validated['before_id'])) {
            $query->forPageBeforeId($messageLimit, $validated['before_id']);
        } else if (isset($validated['after_id'])) {
            $query->forPageAfterId($messageLimit, $validated['after_id']);
        } else {
            $query->limit($messageLimit);
        }
        $messages = $query->get();

        // TODO: Corriger l'algo pour ne pas en avoir besoin
        if (isset($validated['after_id'])) {
            $messages = $messages->reverse();
        }

        $messageBoundary = Message::query()
            ->selectRaw('MIN(id) as min_id, MAX(id) as max_id')
            ->where('conversation_id', $conversation->id)
            ->first();

        return response()->json([
            'messages' => MessageResource::collection($messages),
            'firstMessageId' => $messageBoundary->min_id,
            'lastMessageId' => $messageBoundary->max_id,
        ]);
    }

    public function store(Conversation $conversation, MessageRequest $request)
    {
        // $this->authorize('view', $conversation);

        $validated = $request->validated();

        $message = new Message();
        $message->content = $validated['content'];
        $message->ip = $request->ip();
        $message->sender()->associate(Auth::user()->id);
        $message->conversation()->associate($conversation);
        $message->save();

        return response()->json([
            'message' => MessageResource::make($message)
        ]);
    }

    public function update(Message $message, MessageRequest $request)
    {
        // $this->authorize('update', $message);

        $validated = $request->validated();

        $message->update($validated);
        $message->save();

        return response()->json([
            'message' => MessageResource::make($message)
        ]);
    }

    public function destroy(Message $message)
    {
        // $this->authorize('delete', $message);

        $message->is_archived = true;
        $message->save();

        return response()->noContent();
    }
}
