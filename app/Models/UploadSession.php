<?php

namespace App\Models;

use App\Traits\Expires;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UploadSession extends Model
{
    use Expires, SoftDeletes;

    public $fillable = [
        'token',
        'expires',
        'upload_expires',
    ];
}
