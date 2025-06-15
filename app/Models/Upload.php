<?php

namespace App\Models;

use App\Traits\Expires;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Upload extends Model
{
    use Expires, SoftDeletes;

    const STATUS_UPLOADING = 'uploading';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    public $fillable = [
        'file_name',
        'size',
        'short_id',
        'status',
        'expires',
    ];

    protected static function booted(): void
    {
        static::creating(function (Upload $upload) {
            $length = 13;
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
            $upload->short_id = substr(bin2hex($bytes), 0, $length);
        });
    }

    public function getUploadFilePath(): string
    {
        return storage_path("app/uploads/" . $this->short_id);
    }
}
