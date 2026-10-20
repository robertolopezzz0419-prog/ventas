<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    private function reglas(): array
    {
        return [
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'precio'      => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
        ];
    }

    // Muestra la tabla; si viene ?editar=ID carga ese producto en el formulario
    public function index(Request $request)
    {
        $productos = Producto::orderBy('id', 'desc')->get();
        $editar = $request->has('editar') ? Producto::find($request->editar) : null;

        return view('productos', compact('productos', 'editar'));
    }

    public function store(Request $request)
    {
        Producto::create($request->validate($this->reglas()));

        return redirect()->route('productos.index')->with('ok', 'Producto registrado.');
    }

    public function update(Request $request, Producto $producto)
    {
        $producto->update($request->validate($this->reglas()));

        return redirect()->route('productos.index')->with('ok', 'Producto actualizado.');
    }

    public function destroy(Producto $producto)
    {
        try {
            $producto->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withErrors('No se puede eliminar: el producto ya está en una venta.');
        }

        return redirect()->route('productos.index')->with('ok', 'Producto eliminado.');
    }
}
