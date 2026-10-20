<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $ventas = Venta::with(['cliente', 'detalles.producto'])->orderBy('id', 'desc')->get();
        $clientes = Cliente::orderBy('nombre')->get();
        $productos = Producto::orderBy('nombre')->get();
        $editar = $request->has('editar') ? Venta::find($request->editar) : null;

        return view('ventas', compact('ventas', 'clientes', 'productos', 'editar'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'cliente_id'          => 'required|exists:clientes,id',
            'fecha_venta'         => 'required|date',
            'estado'              => 'required|in:pendiente,confirmada,cancelada',
            'items'               => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad'    => 'required|integer|min:1',
        ]);

        // Si el mismo producto viene en varias filas, se suman las cantidades
        $lineas = [];
        foreach ($datos['items'] as $item) {
            $lineas[$item['producto_id']] = ($lineas[$item['producto_id']] ?? 0) + (int) $item['cantidad'];
        }

        try {
            DB::transaction(function () use ($datos, $lineas) {
                $productos = Producto::whereIn('id', array_keys($lineas))->lockForUpdate()->get()->keyBy('id');

                // Validar stock ANTES de crear la venta
                foreach ($lineas as $productoId => $cantidad) {
                    $producto = $productos[$productoId];
                    if ($producto->stock <= 0) {
                        throw new \RuntimeException("El producto \"{$producto->nombre}\" no tiene stock.");
                    }
                    if ($producto->stock < $cantidad) {
                        throw new \RuntimeException("Stock insuficiente de \"{$producto->nombre}\" (disponible: {$producto->stock}).");
                    }
                }

                $venta = Venta::create([
                    'cliente_id'  => $datos['cliente_id'],
                    'fecha_venta' => $datos['fecha_venta'],
                    'estado'      => $datos['estado'],
                    'total'       => 0,
                ]);

                $totalVenta = 0;
                foreach ($lineas as $productoId => $cantidad) {
                    $producto = $productos[$productoId];
                    $subtotal = $producto->precio * $cantidad;

                    DetalleVenta::create([
                        'venta_id'        => $venta->id,
                        'producto_id'     => $productoId,
                        'cantidad'        => $cantidad,
                        'precio_unitario' => $producto->precio,
                        'total'           => $subtotal,
                    ]);

                    // Una venta cancelada no descuenta stock
                    if ($datos['estado'] !== 'cancelada') {
                        $producto->decrement('stock', $cantidad);
                    }
                    $totalVenta += $subtotal;
                }

                $venta->update(['total' => $totalVenta]);
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors($e->getMessage())->withInput();
        }

        return redirect()->route('ventas.index')->with('ok', 'Venta registrada.');
    }

    // Actualiza cliente, fecha y estado; el stock se ajusta según el cambio de estado
    public function update(Request $request, Venta $venta)
    {
        $datos = $request->validate([
            'cliente_id'  => 'required|exists:clientes,id',
            'fecha_venta' => 'required|date',
            'estado'      => 'required|in:pendiente,confirmada,cancelada',
        ]);

        try {
            DB::transaction(function () use ($venta, $datos) {
                $venta->load('detalles.producto');
                $antes = $venta->estado;
                $despues = $datos['estado'];

                if ($antes !== 'cancelada' && $despues === 'cancelada') {
                    // Se cancela: regresar stock
                    foreach ($venta->detalles as $d) {
                        $d->producto->increment('stock', $d->cantidad);
                    }
                } elseif ($antes === 'cancelada' && $despues !== 'cancelada') {
                    // Se reactiva: volver a validar y descontar stock
                    foreach ($venta->detalles as $d) {
                        $producto = Producto::lockForUpdate()->find($d->producto_id);
                        if ($producto->stock < $d->cantidad) {
                            throw new \RuntimeException("Stock insuficiente de \"{$producto->nombre}\" para reactivar la venta.");
                        }
                        $producto->decrement('stock', $d->cantidad);
                    }
                }

                $venta->update($datos);
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors($e->getMessage());
        }

        return redirect()->route('ventas.index')->with('ok', 'Venta actualizada.');
    }

    // Solo se eliminan ventas pendientes o canceladas
    public function destroy(Venta $venta)
    {
        if (!in_array($venta->estado, ['pendiente', 'cancelada'])) {
            return back()->withErrors('Solo se pueden eliminar ventas pendientes o canceladas.');
        }

        DB::transaction(function () use ($venta) {
            // Una pendiente tenía stock descontado: se devuelve
            if ($venta->estado === 'pendiente') {
                foreach ($venta->detalles()->with('producto')->get() as $d) {
                    $d->producto->increment('stock', $d->cantidad);
                }
            }
            $venta->delete(); // los detalles se borran por cascade
        });

        return redirect()->route('ventas.index')->with('ok', 'Venta eliminada.');
    }
}
