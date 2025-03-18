<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'schedule_id',
        'schedule_name',
        'p_total_number',
    ];

    /**
     * Get the orders for the schedule.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    
    /**
     * Get the total ordered quantity for this schedule.
     */
    public function getTotalOrderedQuantityAttribute()
    {
        return $this->orders->sum('p_quantity');
    }
    
    /**
     * Get the remaining available quantity for this schedule.
     */
    public function getRemainingQuantityAttribute()
    {
        return $this->p_total_number - $this->total_ordered_quantity;
    }
} 