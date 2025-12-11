<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TaxClientSalaryItem extends Model
{
    protected $guarded = [];
    protected $casts = ['allowances_breakdown' => 'array'];
    
    public function sheet() { return $this->belongsTo(TaxClientSalarySheet::class, 'salary_sheet_id'); }
    public function employee() { return $this->belongsTo(TaxClientEmployee::class, 'tax_client_employee_id'); }
}