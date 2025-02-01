<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'total_amount',
        'payment_status',
        'branch_id',
        'user_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->branch_id = Auth::user()->branch_id;
            $model->order_number = Carbon::now()->format('mdHis');
            $model->user_id = Auth::user()->id;
        });

        // Global scope to filter records based on the user's role
        static::addGlobalScope('branch', function ($query) {
            $user = Auth::user();

            // Apply branch filter only if the user is not an admin
            if ($user->role !== 'admin') {
                $query->where('branch_id', $user->branch_id);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Relationship with Customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relationship with SaleItems
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
}
