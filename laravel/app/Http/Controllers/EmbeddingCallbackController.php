<?php

namespace App\Http\Controllers;

use App\Enums\FileStatus;
use App\Events\FileStatusUpdated;
use App\Models\File;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmbeddingCallbackController extends Controller
{

    public function handle(Request $request)
    {
        try{
            $body = $request->getContent();

            $expectedSignature = hash_hmac(
                'sha256',
                $body,
                config('services.embedding.api_key')
            );


            $receivedSignature = $request->header('X-Signature');

            if (! hash_equals($expectedSignature, $receivedSignature)) {
                return response()->json([
                    'message' => 'Invalid signature'
                ], 401);
            }

            $data = $request->all();

            $file = File::find($data['file_id']);

            if(!$file){
                 return response()->json([
                    'message' => 'File not found'
                ], 404);
            }
            $status = FileStatus::tryFrom($data['status']);

            if (!$status) {
                return response()->json([
                    'message' => 'Invalid status'
                ], 422);
            }

            $file->update(['status' => $status]);
            broadcast(new FileStatusUpdated($file));
            return response()->json([
                    'message' => 'File Status Completed'
                ], 200);
        }catch(\Throwable $e){
            Log::error($e);

            return response()->json([
                'message' => 'Internal Server Error'
            ], 500);

        }
    }
}
