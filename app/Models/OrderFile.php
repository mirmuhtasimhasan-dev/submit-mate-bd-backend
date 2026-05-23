<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'file_type',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}