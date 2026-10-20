@extends('layouts.app')
@section('titulo', 'Productos')

@section('contenido')
<h1 class="text-2xl font-bold mb-4">Productos</h1>
{{--Formulario: registrar o actualizar--}}
<form method="POST"
      action="{{ $editar ? route('productos.update', $editar) : route('productos.store') }}"
      class="bg-white p-4 rounded shadow mb-6 grid grid-cols-1 md:grid-cols-4 gap-3">
    @csrf
    @if ($editar) @method('PUT') @endif

    <input name="nombre" placeholder="Nombre" value="{{ old('nombre', $editar->nombre ?? '') }}" class="border rounded px-3 py-2" required>
    <input name="descripcion" placeholder="Descripcion" value="{{ old('descripcion', $editar->descripcion ?? '') }}" class="border rounded px-3 py-2">
    <input name="precio" type="number" step="0.01" min="0" placeholder="Precio" value="{{ old('precio', $editar->precio ?? '') }}" class="border rounded px-3 py-2" required>
    <input name="stock" type="number" min="0" placeholder="Stock" value="{{ old('stock', $editar->stock ?? '') }}" class="border rounded px-3 py-2" required>

    <div class="md:col-span-4 flex gap-2">
        <button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
            {{ $editar ? 'Actualizar' : 'Registrar' }}
        </button>
        @if ($editar)
            <a href="{{ route('productos.index') }}" class="px-4 py-2 rounded border">Cancelar</a>
        @endif
    </div>
</form>

{{-- Tabla de productos --}}
<div class="bg-white rounded shadow overflow-x-auto">
    <table class="w-full text-left">
        <thead class="bg-gray-200">
            <tr>
                <th class="p-3">ID</th><th class="p-3">Nombre</th><th class="p-3">Descripcion</th>
                <th class="p-3">Precio</th><th class="p-3">Stock</th><th class="p-3">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($productos as $p)
                <tr class="border-t">
                    <td class="p-3">{{ $p->id }}</td>
                    <td class="p-3">{{ $p->nombre }}</td>
                    <td class="p-3">{{ $p->descripcion }}</td>
                    <td class="p-3">${{ number_format($p->precio, 2) }}</td>
                    <td class="p-3 {{ $p->stock == 0 ? 'text-red-600 font-semibold' : '' }}">{{ $p->stock }}</td>
                    <td class="p-3 flex gap-2">
                        <a href="{{ route('productos.index', ['editar' => $p->id]) }}" class="text-indigo-600 hover:underline">Editar</a>
                        <form method="POST" action="{{ route('productos.destroy', $p) }}" onsubmit="return confirm('¿Eliminar este producto?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-3 text-center text-gray-500">Sin productos registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
