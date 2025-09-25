<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GSSRemitted extends Model
{
    use HasFactory;

    protected $table = 'gssremitted'; // ✅ matches your database table

    protected $fillable = [
        'bpno',
        'last_name',
        'first_name',
        'mi',
        'basic_monthly_salary',
        'effectivity_date',
        'ps',
        'gs',
        'ec',
        'consoloan',
        'emrglyn',
        'plreg',
        'gfal',
        'mpl',
        'mpl_lite',
        'orno',
        'datepaid',
        'my_covered',
    ];
}
