<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Ajusta $fillable a las columnas reales de tu migración de productos.
class Producto extends Model
{
    protected $fillable = ['nombre', 'descripcion', 'precio', 'stock'];
}
