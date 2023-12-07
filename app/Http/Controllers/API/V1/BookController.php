<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Book;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Http\Requests\StoreBookRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateBookRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = Book::latest()->paginate(5);
        if($books) {
            foreach($books as $book){
                $book->cover = base64_decode($book->cover);
            }    
            return new BookResource(true, 'List Data Posts', $books);
        }
        return response()->json([
            'success' => false
        ], 409);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request)
    {
        $data = $request->validated();

        if (!$data) {
            return response()->json($validator->errors(), 422);
        }

        $books = Book::All();
        $exist = false;
        foreach($books as $book){
            if($book->title == $request->title && $book->writer == $request->writer){
                $exist = true;
                break;
            }
        } 

        if($exist){
            //return JSON process insert failed 
            return response()->json([
                'success' => false,
                'message' => 'Buku dengan judul "' . $request->title . '" yang ditulis oleh "' . $request->writer . '" sudah ada.'
            ], 409);
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
            return new BookResource(true, 'Data Book Berhasil Ditambahkan!', $book);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            $book = Book::findOrFail($id);
            return new BookResource(true, 'Data Book Ditemukan!', $book);
        } catch(ModelNotFoundException $e){
            return response()->json([
                'success' => false,
                'message' => 'Data Book Tidak Ditemukan!'
            ], 404);
        }
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

        $books = Book::excludedId($id)->get();
        if($books){
            $exist = false;
            foreach($books as $book){
                if($book->title == $request->title && $book->writer == $request->writer){
                    $exist = true;
                    break;
                }
            }
            if($exist){
                //return JSON process insert failed 
                return response()->json([
                    'success' => false,
                    'message' => 'Buku dengan judul "' . $request->title . '" yang ditulis oleh "' . $request->writer . '" sudah ada.'
                ], 409);
            }
        }

        $book = Book::findOrFail($id);
            
        if($request->hasFile('cover')){
            // Menyimpan file ke storage
            $path = $request->file('cover')->store('cover', 'public');
        
            // Mengambil URL untuk file
            $url = base64_encode(Storage::url($path));
            
            $book->cover = base64_decode($book->cover);
            Storage::delete('/public'.str_replace('/storage', '', $book->cover));
        
            $book->update([
                'title' => $request->title,
                'stock' => $request->stock,
                'cover' => $url,
                'description' => $request->description,
                'writer' => $request->writer
            ]);
        } else{
            $book->update([
                'title' => $request->title,
                'stock' => $request->stock,
                'description' => $request->description,
                'writer' => $request->writer
            ]);
        }
        return new BookResource(true, 'Data Book Berhasil Diubah!', $book);
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
