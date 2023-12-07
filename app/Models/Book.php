<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'book_code',
        'title',
        'stock',
        'cover',
        'writer',
    ];

    public function scopeExcludedId($query, $excludedId)
    {
        return $query->where('id', '!=', $excludedId);
    }
}
