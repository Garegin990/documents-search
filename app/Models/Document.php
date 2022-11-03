<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'extension',
        'content',
    ];

    protected $appends = [
        'created_date'
    ];

    public function getCreatedDateAttribute() {
        return $this->created_at->format('Y:m:d H:i:s');
    }
}
