<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CRemitted extends Model
{
    protected $table = 'c_remitted';

    protected $fillable = [
        'record_type',
        'pagibig_acctno',
        'employee_id',
        'last_name',
        'first_name',
        'middle_name',
        'employee_contribution',
        'employer_contribution',
        'tin',
        'birthdate',
        'orno',
        'date',
        'my_covered',
    ];
}
