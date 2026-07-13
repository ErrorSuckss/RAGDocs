<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class ChatController extends Controller
{
    //
    public function index(){
        return Inertia::render('Chat');
    }

    public function query(ChatRequest $request){
        try{
            $request->validated();
            $query = $request['query'];
            $response = Http::timeout(300)
            ->post('http://127.0.0.1:8001/query',['query' => $query]);
            $response->throw();

            return response()->json(['response' => $response->json('answer')], Response::HTTP_OK);
            
        }catch(\Throwable $e){
            Log::error(['query_error' => $e->getMessage()]);
            return response()->json(['error' => $e], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
