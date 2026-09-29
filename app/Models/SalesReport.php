<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReport extends Model
{
    protected $fillable = [
        'report_date', 'orders_count', 'items_qty', 'gross_revenue', 'computed_at',
    ];

    protected $casts = [
        'report_date' => 'date',
        'gross_revenue' => 'decimal:2',
        'computed_at' => 'datetime',
    ];
}
