<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'buying_price',
        'selling_price',
        'quantity',
        'stock_category_id',
        'supplier_id',
        'batch_no',
        'expiry_date',
        'branch_id',
        'user_id',
        'stock_quantity',
        'measurement_unit'
    ];

    protected $attributes = [
        'stock_quantity' => 0,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->branch_id = Auth::user()->branch_id;
            $model->user_id = Auth::user()->id;

            if (is_null($model->stock_quantity)) {
                $model->stock_quantity = 0;
            }
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

    public function stockCategory()
    {
        return $this->belongsTo(StockCategory::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function getNameAttribute($value)
    {
        return \Str::title($value);
    }
}
