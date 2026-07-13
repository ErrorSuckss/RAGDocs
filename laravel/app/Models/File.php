<?php

namespace App\Models;

use App\Enums\FileStatus;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        'name',
        'file_path',
        'file_type',
        'status',
    ];

    protected $casts = [
        'status' => FileStatus::class,
    ];


    public $timestamps = false;
}
