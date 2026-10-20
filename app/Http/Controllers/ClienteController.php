<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    private function reglas(?int $id = null): array
    {
        return [
            'nombre'    => 'required|string|max:255',
            'email'     => ['required', 'email', 'max:255', Rule::unique('clientes', 'email')->ignore($id)],
            'direccion' => 'required|string|max:255',
            'telefono'  => ['required', 'regex:/^[0-9+\-\s]{7,20}$/'],
        ];
    }

    public function index(Request $request)
    {
        $clientes = Cliente::orderBy('id', 'desc')->get();
        $editar = $request->has('editar') ? Cliente::find($request->editar) : null;

        return view('clientes', compact('clientes', 'editar'));
    }

    public function store(Request $request)
    {
        Cliente::create($request->validate($this->reglas()));

        return redirect()->route('clientes.index')->with('ok', 'Cliente registrado.');
    }

    public function update(Request $request, Cliente $cliente)
    {
        $cliente->update($request->validate($this->reglas($cliente->id)));

        return redirect()->route('clientes.index')->with('ok', 'Cliente actualizado.');
    }

    public function destroy(Cliente $cliente)
    {
        if ($cliente->ventas()->exists()) {
            return back()->withErrors('No se puede eliminar: el cliente tiene ventas registradas.');
        }

        $cliente->delete();

        return redirect()->route('clientes.index')->with('ok', 'Cliente eliminado.');
    }
}
