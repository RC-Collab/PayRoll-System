<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalarySlip extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'salary_id',
        'slip_number',
        'components_breakdown',
        'attendance_summary',
        'tax_calculation',
        'notes',
        'issued_by',
        'issue_date',
        'payment_method',
        'transaction_id',
        'bank_name',
        'account_number',
    ];

    protected $casts = [
        'components_breakdown' => 'array',
        'attendance_summary' => 'array',
        'tax_calculation' => 'array',
        'issue_date' => 'date',
    ];

    // Relationships
    public function salary()
    {
        return $this->belongsTo(MonthlySalary::class, 'salary_id');
    }

    // Accessors
    public function getFormattedIssueDateAttribute()
    {
        return $this->issue_date->format('d M, Y');
    }

    public function getTotalAllowancesAttribute()
    {
        $breakdown = $this->components_breakdown ?? [];
        $total = 0;
        
        foreach ($breakdown as $component) {
            if ($component['type'] === 'allowance') {
                $total += $component['amount'];
            }
        }
        
        return $total;
    }

    public function getTotalDeductionsAttribute()
    {
        $breakdown = $this->components_breakdown ?? [];
        $total = 0;
        
        foreach ($breakdown as $component) {
            if ($component['type'] === 'deduction') {
                $total += $component['amount'];
            }
        }
        
        return $total;
    }

    // Generate slip number
    public static function generateSlipNumber()
    {
        $date = now()->format('Ym');
        $count = self::where('slip_number', 'like', "SLIP-{$date}-%")->count() + 1;
        return "SLIP-{$date}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}