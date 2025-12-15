<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    // Indicar que no tiene clave primaria autoincremental
    public $incrementing = false;
    
    // No hay una única primary key, es compuesta
    protected $primaryKey = null;
    
    protected $fillable = [
        'user_id',
        'product_id',
    ];  

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
}
