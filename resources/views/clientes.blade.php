@extends('layouts.app')
@section('titulo', 'Clientes')

@section('contenido')
<h1 class="text-2xl font-bold mb-4">Clientes</h1>

<form method="POST"
      action="{{ $editar ? route('clientes.update', $editar) : route('clientes.store') }}"
      class="bg-white p-4 rounded shadow mb-6 grid grid-cols-1 md:grid-cols-2 gap-3">
    @csrf
    @if ($editar) @method('PUT') @endif

    <input name="nombre" placeholder="Nombre" value="{{ old('nombre', $editar->nombre ?? '') }}" class="border rounded px-3 py-2" required>
    <input name="email" type="email" placeholder="Email" value="{{ old('email', $editar->email ?? '') }}" class="border rounded px-3 py-2" required>
    <input name="direccion" placeholder="Direccion" value="{{ old('direccion', $editar->direccion ?? '') }}" class="border rounded px-3 py-2" required>
    <input name="telefono" placeholder="Telefono" value="{{ old('telefono', $editar->telefono ?? '') }}" class="border rounded px-3 py-2" required>

    <div class="md:col-span-2 flex gap-2">
        <button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
            {{ $editar ? 'Actualizar' : 'Registrar' }}
        </button>
        @if ($editar)
            <a href="{{ route('clientes.index') }}" class="px-4 py-2 rounded border">Cancelar</a>
        @endif
    </div>
</form>

<div class="bg-white rounded shadow overflow-x-auto">
    <table class="w-full text-left">
        <thead class="bg-gray-200">
            <tr>
                <th class="p-3">ID</th><th class="p-3">Nombre</th><th class="p-3">Email</th>
                <th class="p-3">Direccion</th><th class="p-3">Telefono</th><th class="p-3">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($clientes as $c)
                <tr class="border-t">
                    <td class="p-3">{{ $c->id }}</td>
                    <td class="p-3">{{ $c->nombre }}</td>
                    <td class="p-3">{{ $c->email }}</td>
                    <td class="p-3">{{ $c->direccion }}</td>
                    <td class="p-3">{{ $c->telefono }}</td>
                    <td class="p-3 flex gap-2">
                        <a href="{{ route('clientes.index', ['editar' => $c->id]) }}" class="text-indigo-600 hover:underline">Editar</a>
                        <form method="POST" action="{{ route('clientes.destroy', $c) }}" onsubmit="return confirm('Eliminar este cliente?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-3 text-center text-gray-500">Sin clientes registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
