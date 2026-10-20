<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo') - Sistema de Ventas</title>
    {{-- Tailwind por CDN para no depender de Vite; si ya usas Vite, cámbialo por @vite --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
    <nav class="bg-indigo-700 text-white">
        <div class="max-w-6xl mx-auto px-4 py-3 flex gap-6">
            <a href="{{ route('productos.index') }}" class="font-semibold hover:underline">Productos</a>
            <a href="{{ route('clientes.index') }}" class="font-semibold hover:underline">Clientes</a>
            <a href="{{ route('ventas.index') }}" class="font-semibold hover:underline">Ventas</a>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto p-4">
        @if (session('ok'))
            <div class="mb-4 rounded bg-green-100 border border-green-300 text-green-800 px-4 py-2">{{ session('ok') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded bg-red-100 border border-red-300 text-red-800 px-4 py-2">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('contenido')
    </main>
</body>
</html>
