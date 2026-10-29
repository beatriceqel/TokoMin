<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $table = 'transactions'; // Menyesuaikan dengan tabel transaksi di database Anda

    protected $guarded = ['id'];

    // Relasi ke detail item pesanan
    public function items()
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id', 'id');
    }

    // Relasi ke customer / user
    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}