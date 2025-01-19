<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    public function getConversations()
    {
        $conversations = Conversation::query()
            ->with(['requester', 'user'])
            ->select('conversations.*', 'm.content as message_content', 'm.created_at as message_created_at')
            ->leftJoin((new Message())->getTable() . ' as m', function ($q) {
                $q->on('m.conversation_id', '=', 'conversations.id')
                    ->where('m.id', '=', DB::raw('(select max(id) from messages where messages.conversation_id = conversations.id)'));
            })
            ->isVisible()
            ->get();

        return response()->json([
            'conversations' => ConversationResource::collection($conversations)
        ]);
    }

    public function getOrCreate(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:' . (new User())->getTable() . ',id']
        ]);

        $conversation = Conversation::getConversation($validated['user_id']);
        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->requester()->associate(Auth::user()->id);
            $conversation->user()->associate($validated['user_id']);
            $conversation->save();
        }

        return response()->json([
            'conversation' => ConversationResource::make($conversation)
        ]);
    }

    public function hide(Conversation $conversation)
    {
        // $this->authorize('view', $conversation);

        if ($conversation->requester_id === Auth::user()->id) {
            $conversation->is_closed_requester = true;
        } else {
            $conversation->is_closed_user = true;
        }
        $conversation->save();

        return response()->noContent();
    }
}
