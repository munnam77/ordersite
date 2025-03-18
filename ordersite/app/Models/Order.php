<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_id',
        'schedule_id',
        'schedule_name',
        'p_quantity',
        'comment',
        'delivery_date',
        'vehicle',
        'working_day',
        'working_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'delivery_date' => 'date',
        'working_day' => 'datetime',
        'working_time' => 'datetime',
        'p_quantity' => 'float',
    ];

    /**
     * Get the store that owns the order.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the schedule that owns the order.
     */
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
} 