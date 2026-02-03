<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAttachment extends Model
{
    public $timestamps = false; // Karena kita punya kolom manual uploaded_at
    protected $guarded = ['id'];
    protected $casts = ['uploaded_at' => 'datetime'];
}
