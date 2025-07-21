<?php

namespace App\Http\Controllers;

use App\Http\Resources\SurveyResource;
use App\Models\Survey;
use App\Models\SurveyOption;
use App\Models\SurveyOptionReply;
use Illuminate\Support\Facades\Auth;

class SurveyController extends Controller
{
    public function reply(SurveyOption $surveyOption)
    {
        $surveyOptionReply = SurveyOptionReply::query()
            ->whereHas('surveyOption', function ($q) use ($surveyOption) {
                $q->where('survey_id', $surveyOption->survey_id);
            })
            ->where('user_id', Auth::user()->id)
            ->first();

        if ($surveyOptionReply) {
            $surveyOptionReply->surveyOption()->associate($surveyOption);
            $surveyOptionReply->save();
        } else {
            $surveyOptionReply = new SurveyOptionReply();
            $surveyOptionReply->user()->associate(Auth::user()->id);
            $surveyOptionReply->surveyOption()->associate($surveyOption);
            $surveyOptionReply->save();
        }

        $survey = Survey::query()
            ->where('id', $surveyOption->survey_id)
            ->with([
                'surveyOptions' => function ($q) {
                    $q->withCount('surveyOptionReplies')
                        ->withExists(['surveyOptionReplies' => function ($q) {
                        $q->where('user_id', Auth::user()->id);
                    }]);
                }
            ])->first();

        return response()->json(['survey' => SurveyResource::make($survey)]);
    }

    public function deleteReply(SurveyOption $surveyOption)
    {
        SurveyOptionReply::query()
            ->where('user_id', Auth::user()->id)
            ->where('survey_option_id', $surveyOption->id)->delete();

        $survey = Survey::query()
            ->where('id', $surveyOption->survey_id)
            ->with([
                'surveyOptions' => function ($q) {
                    $q->withCount('surveyOptionReplies')
                        ->withExists(['surveyOptionReplies' => function ($q) {
                        $q->where('user_id', Auth::user()->id);
                    }]);
                }
            ])->first();

        return response()->json(['survey' => SurveyResource::make($survey)]);
    }
}