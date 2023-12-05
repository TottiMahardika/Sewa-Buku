<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Http\Requests\StoreBookRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateBookRequest;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Book::All();
    
        if($books) {
            foreach($books as $book){
                $book->cover = base64_decode($book->cover);
            }    
            return response()->json([
                'success' => true,
                'book'    => BookResource::collection($books),  
            ], 201);
        }
        return response()->json([
            'success' => false,
        ], 409);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (!$data) {
            return response()->json($validator->errors(), 422);
        }

        // Menyimpan file ke storage
        $path = $request->file('cover')->store('cover', 'public');
        
        // Mengambil URL untuk file
        $url = base64_encode(Storage::url($path));
        
        //create book
        $book = Book::create([
            'book_code' => $request->book_code,
            'title' => $request->title,
            'stock' => $request->stock,
            'cover' => $url,
            'description' => $request->description,
            'writer' => $request->writer
        ]);

        //return response JSON user is created
        if($book) {
            return response()->json([
                'success' => true,
                'book'    => $book,  
            ], 201);
        }

        //return JSON process insert failed 
        return response()->json([
            'success' => false,
        ], 409);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $book = Book::findOrFail($id);
        if($book) {
            $book->cover = base64_decode($book->cover);
            return response()->json([
                'success' => true,
                'book'    => $book,  
            ], 201);
        }
        return response()->json([
            'success' => false,
        ], 409);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request, string $id)
    {
        $data = $request->validated();

        if (!$data) {
            return response()->json($validator->errors(), 422);
        }

        $book = Book::findOrFail($id);
        
        if($book){
            $book->cover = base64_decode($book->cover);
            Storage::delete($book->cover);

             // Menyimpan file ke storage
            $path = $request->file('cover')->store('cover', 'public');
            
            // Mengambil URL untuk file
            $url = base64_encode(Storage::url($path));
        
            $updated = $book->update([
                'title' => $request->title,
                'stock' => $request->stock,
                'cover' => $url,
                'description' => $request->description,
                'writer' => $request->writer
            ]);

            if($updated){
                return response()->json([
                    'success' => true,
                    'book'    => $book,  
                ], 201);
            }
            return response()->json([
                'success' => false,
            ], 409);
        }
        return response()->json([
            'success' => false,
        ], 409);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $book = Book::findOrFail($id);
        $book->cover = base64_decode($book->cover);
        if (Storage::exists('/public'.str_replace('/storage', '', $book->cover))) {
            Storage::delete('/public'.str_replace('/storage', '', $book->cover));
        }

        $delete = $book->delete();
        if($delete){
            return response()->json([
                'success' => true,
            ], 201);
        }
        return response()->json([
            'success' => false,
        ], 409);
    }
}
