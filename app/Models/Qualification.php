<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Qualification extends Model
{
    use HasFactory;

    protected $table = 'qualifications';

    protected $fillable = [
        'employee_id',
        'degree',
        'institution',
        'board',
        'year',
        'percentage',
        'grade',
        'specialization',
        'start_date',
        'end_date',
        'is_pursuing',
        'certificate_path'
    ];

    protected $casts = [
        'year' => 'integer',
        'percentage' => 'float',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_pursuing' => 'boolean'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
