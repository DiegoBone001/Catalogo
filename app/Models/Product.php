<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $fillable = [ // Campos que se pueden llenar
        'nombre', // Nombre del producto
        'descripcion', // Descripción del producto
        'precio', // Precio del producto
        'url_imagen', // URL de la imagen del producto
    ];

    public function favorites() 
    {
        return $this->belongsToMany(User::class, 'favorites'); // Relación con el usuario
    }   

    public function comments()
    {
        return $this->hasMany(Comment::class); // Relación con los comentarios
    }
}
