# Módulos: Productos, Clientes y Ventas (Laravel)

## Dónde va cada archivo
Copia cada carpeta dentro de tu proyecto Laravel respetando la ruta:

- `app/Models/*` → Cliente, Venta, DetalleVenta, Producto
- `app/Http/Controllers/*` → ProductoController, ClienteController, VentaController
- `database/migrations/*` → clientes, ventas, detalle_ventas
- `resources/views/*` → layouts/app, productos, clientes, ventas
- `routes/web.php` → copia las rutas a tu archivo (no lo reemplaces si ya tienes otras)

## Importante
1. Ya debes tener la migración de `productos` (nombre, descripcion, precio, stock). Si tus columnas se llaman distinto, ajústalas en Producto.php, ProductoController y las vistas.
2. La migración de `detalle_ventas` depende de `productos`: debe correr después de ella (las fechas de los archivos ya lo cuidan si tu migración de productos es anterior al 2026_10_19).
3. Ejecuta: `php artisan migrate`
4. Corre el servidor: `php artisan serve` y abre /productos, /clientes y /ventas.

## Subir a GitHub
```
git init
git add .
git commit -m "Módulos de productos, clientes y ventas"
git branch -M main
git remote add origin https://github.com/TU_USUARIO/TU_REPO.git
git push -u origin main
```
