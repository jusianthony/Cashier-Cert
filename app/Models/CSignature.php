<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CSignature extends Model
{
    protected $table = 'c_signature'; // exact table name in DB
    protected $fillable = ['full_name', 'designation'];
}
