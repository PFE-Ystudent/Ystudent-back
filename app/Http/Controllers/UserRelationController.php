<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserDetailsResource;
use App\Models\User;
use App\Models\UserRelation;
use App\Models\UserRelationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserRelationController extends Controller
{
    public function getRelations (UserRelationType $userRelationType)
    {
        $relations = User::query()
            ->select('*')
            ->selectRaw( $userRelationType->id . ' as relationType')
            ->withCount(['posts', 'postReplies'])
            ->whereExists(function ($q) use ($userRelationType) {
                $q->select('id')
                    ->from((new UserRelation())->getTable())
                    ->where('user_relation_type_id', $userRelationType->id)
                    ->where(function ($q) use ($userRelationType) {
                        if ($userRelationType->id !== UserRelationType::$blocked && $userRelationType->id !== UserRelationType::$report) {
                            $q->where(function ($q) { // je suis la cible
                                $q->whereColumn('requester_id', 'users.id')
                                ->where('user_id', Auth::user()->id);
                            });
                        }
                        if ($userRelationType->id !== UserRelationType::$request) {
                            $q->orWhere(function ($q) { // j'ai fait la demande
                                $q->where('requester_id', Auth::user()->id)
                                    ->whereColumn('user_id', 'users.id');
                            });
                        }
                    });
                
            })
            ->get();

        return response()->json(UserDetailsResource::collection($relations));
    }

    public function sendRequest (User $user)
    {
        if (!$user->getRelationWith(Auth::user())) {
            $userRelation = new UserRelation();
            $userRelation->requester()->associate(Auth::user()->id);
            $userRelation->userRelationType()->associate(UserRelationType::$request);
            $userRelation->user()->associate($user->id);
    
            $userRelation->save();

            return response()->noContent(201);
        }
        return response()->noContent(409);
    }

    public function replyRequest (User $user, Request $request)
    {
        $validated = $request->validate([
            'is_accepted' => ['required', 'boolean']
        ]);

        $userRelation = $user->getRelationWith(Auth::user());
        if ($userRelation->user_relation_type_id === UserRelationType::$request && $userRelation->user_id === Auth::user()->id) {
            if ($validated['is_accepted']) {
                $userRelation->userRelationType()->associate(UserRelationType::$contact);
                $userRelation->save();
            } else {
                $userRelation->delete();
            }
            return response()->noContent(201);
        }
        return response()->noContent(409);
    }
}
