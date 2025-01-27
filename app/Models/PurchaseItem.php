<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'medicine_id',
        'quantity',
        'price',
        'total',
        'branch_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->branch_id = Auth::user()->branch_id;
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

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
