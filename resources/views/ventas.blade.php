@extends('layouts.app')
@section('titulo', 'Ventas')

@section('contenido')
<h1 class="text-2xl font-bold mb-4">Ventas</h1>

@if ($editar)
    {{--EDITAR: cliente, fecha y estado--}}
    <form method="POST" action="{{ route('ventas.update', $editar) }}"
          class="bg-white p-4 rounded shadow mb-6 grid grid-cols-1 md:grid-cols-3 gap-3">
        @csrf @method('PUT')
        <h2 class="md:col-span-3 font-semibold">Editando venta #{{ $editar->id }}</h2>

        <select name="cliente_id" class="border rounded px-3 py-2" required>
            @foreach ($clientes as $c)
                <option value="{{ $c->id }}" @selected(old('cliente_id', $editar->cliente_id) == $c->id)>{{ $c->nombre }}</option>
            @endforeach
        </select>
        <input type="date" name="fecha_venta" value="{{ old('fecha_venta', $editar->fecha_venta->format('Y-m-d')) }}" class="border rounded px-3 py-2" required>
        <select name="estado" class="border rounded px-3 py-2" required>
            @foreach (['pendiente', 'confirmada', 'cancelada'] as $e)
                <option value="{{ $e }}" @selected(old('estado', $editar->estado) == $e)>{{ ucfirst($e) }}</option>
            @endforeach
        </select>

        <div class="md:col-span-3 flex gap-2">
            <button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Actualizar</button>
            <a href="{{ route('ventas.index') }}" class="px-4 py-2 rounded border">Cancelar</a>
        </div>
    </form>
@else
    {{--REGISTRAR: cabecera + productos --}}
    <form method="POST" action="{{ route('ventas.store') }}" class="bg-white p-4 rounded shadow mb-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
            <select name="cliente_id" class="border rounded px-3 py-2" required>
                <option value="">-- Cliente --</option>
                @foreach ($clientes as $c)
                    <option value="{{ $c->id }}" @selected(old('cliente_id') == $c->id)>{{ $c->nombre }}</option>
                @endforeach
            </select>
            <input type="date" name="fecha_venta" value="{{ old('fecha_venta', date('Y-m-d')) }}" class="border rounded px-3 py-2" required>
            <select name="estado" class="border rounded px-3 py-2" required>
                @foreach (['pendiente', 'confirmada', 'cancelada'] as $e)
                    <option value="{{ $e }}" @selected(old('estado', 'pendiente') == $e)>{{ ucfirst($e) }}</option>
                @endforeach
            </select>
        </div>

        <h2 class="font-semibold mb-2">Productos</h2>
        <div id="lineas" class="space-y-2 mb-3"></div>
        <button type="button" onclick="agregarLinea()" class="text-indigo-600 hover:underline mb-4">+ Agregar producto</button>

        <div class="flex items-center justify-between">
            <span class="text-lg font-semibold">Total: $<span id="total">0.00</span></span>
            <button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Registrar venta</button>
        </div>
    </form>

    <script>
        // Productos disponibles 
       @php
    $productosJs = $productos->map(fn ($p) => [
        'id' => $p->id,
        'nombre' => $p->nombre,
        'precio' => (float) $p->precio,
        'stock' => $p->stock,
    ])->values();
@endphp
const productos = @json($productosJs);
        let contador = 0;

        function agregarLinea() {
            const i = contador++;
            const opciones = productos.map(p =>
                `<option value="${p.id}" data-precio="${p.precio}" ${p.stock <= 0 ? 'disabled' : ''}>
                    ${p.nombre} - $${p.precio.toFixed(2)} (stock: ${p.stock})${p.stock <= 0 ? ' - SIN STOCK' : ''}
                </option>`).join('');
            const div = document.createElement('div');
            div.className = 'flex gap-2';
            div.innerHTML = `
                <select name="items[${i}][producto_id]" class="border rounded px-3 py-2 flex-1" onchange="calcular()" required>
                    <option value="">-- Producto --</option>${opciones}
                </select>
                <input type="number" name="items[${i}][cantidad]" min="1" value="1" class="border rounded px-3 py-2 w-24" oninput="calcular()" required>
                <button type="button" class="text-red-600" onclick="this.parentElement.remove(); calcular()">Quitar</button>`;
            document.getElementById('lineas').appendChild(div);
            calcular();
        }

        function calcular() {
            let total = 0;
            document.querySelectorAll('#lineas > div').forEach(fila => {
                const sel = fila.querySelector('select');
                const cant = parseInt(fila.querySelector('input').value) || 0;
                const precio = parseFloat(sel.selectedOptions[0]?.dataset.precio || 0);
                total += precio * cant;
            });
            document.getElementById('total').textContent = total.toFixed(2);
        }

        agregarLinea();
    </script>
@endif

{{-- Tabla de ventas --}}
<div class="bg-white rounded shadow overflow-x-auto">
    <table class="w-full text-left">
        <thead class="bg-gray-200">
            <tr>
                <th class="p-3">ID</th><th class="p-3">Cliente</th><th class="p-3">Fecha</th>
                <th class="p-3">Estado</th><th class="p-3">Productos</th><th class="p-3">Total</th><th class="p-3">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ventas as $v)
                <tr class="border-t">
                    <td class="p-3">{{ $v->id }}</td>
                    <td class="p-3">{{ $v->cliente->nombre }}</td>
                    <td class="p-3">{{ $v->fecha_venta->format('d/m/Y') }}</td>
                    <td class="p-3">
                        <span class="px-2 py-1 rounded text-sm
                            {{ $v->estado == 'confirmada' ? 'bg-green-100 text-green-800' : ($v->estado == 'cancelada' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($v->estado) }}
                        </span>
                    </td>
                    <td class="p-3">
                        @foreach ($v->detalles as $d)
                            <div class="text-sm">{{ $d->cantidad }} x {{ $d->producto->nombre }}</div>
                        @endforeach
                    </td>
                    <td class="p-3">${{ number_format($v->total, 2) }}</td>
                    <td class="p-3 flex gap-2">
                        <a href="{{ route('ventas.index', ['editar' => $v->id]) }}" class="text-indigo-600 hover:underline">Editar</a>
                        @if (in_array($v->estado, ['pendiente', 'cancelada']))
                            <form method="POST" action="{{ route('ventas.destroy', $v) }}" onsubmit="return confirm('¿Eliminar esta venta?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Eliminar</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-3 text-center text-gray-500">Sin ventas registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
