<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\PublicQuestionAndAnswers;
use Illuminate\Http\Request;

class PublicQuestionAndAnswersController extends Controller
{
    public function getAllQuestions()
    {
        return PublicQuestionAndAnswers::where('question_id', null)
        ->orderBy('created_at', 'DESC')->get();
        
    }
    
    public function getAnswers($questionId)
    {
        return PublicQuestionAndAnswers::with([ 'answers.user','answers'])->find($questionId);
    }
    public function getMyQuestions()
    {
        return PublicQuestionAndAnswers::where('question_id', null)
            ->where('user_id', auth()->user()->id)->get();

    }
    public function ask(Request $request)
    {
        $request->validate([
            'content' => 'required'
        ]);
        $question = new PublicQuestionAndAnswers();
        $question->content = $request->input('content');
        $question->user_id = auth()->user()->id;
        $question->save();
        return response()->json(['success'=> true]);
    }
    public function answer(Request $request)
    {
        $request->validate([
            'question_id' => 'required',
            'content' => 'required'
        ]);
        $answer = new PublicQuestionAndAnswers();
        $answer->content = $request->input('content');
        $answer->user_id = auth()->user()->id;
        $answer->question_id = $request->input('question_id');
        $answer->save();
        return response()->json(['success'=> true]);
    }
}
