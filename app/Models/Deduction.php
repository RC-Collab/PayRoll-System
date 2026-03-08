<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deduction extends Model
{
    // allow mass assignment for the standard deduction fields
    protected $fillable = [
        'name',
        'code',
        'type',
        'default_value',
        'percentage',
        'max_amount',
        'formula',
        'is_mandatory',
        'is_active',
        'sort_order',
        'description',
    ];
}
