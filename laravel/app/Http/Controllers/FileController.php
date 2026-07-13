<?php

namespace App\Http\Controllers;

use App\Enums\FileStatus;
use App\Http\Requests\FileRequest;
use App\Http\Resources\FileResource;
use App\Jobs\ProcessFileEmbedding;
use App\Models\File;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $files = File::paginate(3);
        return Inertia::render('Welcome', ['files' => FileResource::collection($files)]);
    }
    public function document()
    {
        $files = File::paginate(10);
        return Inertia::render('Documents', ['files' => FileResource::collection($files)]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FileRequest $request)
    {
        $request->validated();

        $uploadedFile = $request->file('file');
        $path = $uploadedFile->store('files', 'public');
        try{
            $file = DB::transaction(function () use ($uploadedFile, $path) {
                return File::create([
                    'name' => $uploadedFile->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $uploadedFile->getClientMimeType(),
                    'status' => FileStatus::Pending,
                ]);
            });

            ProcessFileEmbedding::dispatch($file);

            return response()->json([
                'message' => 'File uploaded and processing started.',
                'file' => new FileResource($file)
                ], Response::HTTP_CREATED);
        } catch(\Throwable $e){
            Storage::disk('public')->delete($path);
            return response()->json([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    public function python(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
        ]);
        $response = Http::post('http://localhost:8001/python-rag', [
            'text' => $request->input('text')
        ]);

        return response()->json(['response' => $response->json()], Response::HTTP_OK);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(File $file)
    {
        try{
            DB::transaction(function () use ($file){
                $response = Http::timeout(30)
                    ->delete('http://127.0.0.1:8001/delete',[
                            'file_id' => $file->id,
                            'file_name' => $file->name,
                    ]);
                    
                $response->throw();

                if(Storage::disk('public')->exists($file->file_path)){
                    Storage::disk('public')->delete($file->file_path);
                }
                    
                $file->delete();
                    
                return response()->json([
                    'message' => 'File deleted successfully.'
                ], Response::HTTP_OK);
            });

        }catch (Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                    'message' => 'File not deleted.'
                ], Response::HTTP_CONFLICT);
        }
        
    }
}
