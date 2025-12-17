<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-white drop-shadow-lg">
            👥 Participantes
        </h2>
    </x-slot>

    {{-- CONTENEDOR CON FONDO --}}
    <div class="min-h-screen bg-[url('/matute-luces-navidenas.png')] bg-cover bg-center bg-fixed">

        {{-- CAPA OSCURA PARA LEGIBILIDAD --}}
        <div class="min-h-screen bg-black/60 py-6">

            <div class="max-w-7xl mx-auto px-4">

                {{-- BORRAR PREMIOS --}}
                <form method="POST"
                      action="{{ route('participantes.borrarTodo') }}"
                      onsubmit="return confirm('¿Seguro que deseas borrar TODOS los premios?')"
                      class="mb-4 inline-block">
                    @csrf
                    @method('DELETE')

                    <button class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        🗑️ Borrar todos los premios
                    </button>
                </form>

                {{-- IMPORTAR CSV --}}
                <form method="POST"
                      action="{{ route('participantes.importar') }}"
                      enctype="multipart/form-data"
                      class="mb-6 flex flex-wrap items-center gap-4">
                    @csrf
                    <input type="file" name="archivo" required>
                    <button class="bg-blue-600 text-white px-4 py-2 rounded">
                        Importar CSV
                    </button>
                </form>

                {{-- BÚSQUEDA --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold mb-1 text-white">
                        Buscar por nombre
                    </label>
                    <input
                        type="text"
                        id="buscar"
                        placeholder="Escribe un nombre..."
                        class="border rounded px-3 py-2 w-64"
                    >
                </div>

                {{-- TABLA --}}
                <div id="tabla-participantes" class="bg-white rounded-xl shadow-lg p-4">
                    @include('participantes._tabla')
                </div>

            </div>
        </div>
    </div>

    {{-- JS --}}
    <script>
        const buscarInput = document.getElementById('buscar');
        const tabla = document.getElementById('tabla-participantes');

        let timeout = null;

        function buscar(url = null) {
            const buscar = buscarInput.value;
            const endpoint = url ?? `{{ route('participantes.buscar') }}?` +
                new URLSearchParams({ buscar });

            fetch(endpoint)
                .then(res => res.text())
                .then(html => tabla.innerHTML = html);
        }

        buscarInput.addEventListener('keyup', () => {
            clearTimeout(timeout);
            timeout = setTimeout(buscar, 300);
        });

        document.addEventListener('click', e => {
            const link = e.target.closest('.pagination a');
            if (link) {
                e.preventDefault();
                buscar(link.href);
            }
        });
    </script>
</x-app-layout>
