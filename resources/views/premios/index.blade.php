<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">🎁 Premios</h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto">
        <form method="POST"
            action="{{ route('premios.borrarTodo') }}"
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
              action="{{ route('premios.importar') }}"
              enctype="multipart/form-data"
              class="mb-6 flex flex-wrap items-center gap-4">
            @csrf
            <input type="file" name="archivo" required>
            <button class="bg-green-600 text-white px-4 py-2 rounded">
                Importar CSV
            </button>
        </form>

        {{-- FILTRO --}}
        <form method="GET"
              action="{{ route('premios.index') }}"
              class="mb-6 flex flex-wrap items-end gap-4">

            <div>
                <label class="block text-sm font-semibold mb-1">Estado</label>
                <select name="estado" class="border rounded px-3 py-2">
                    <option value="">Todos</option>
                    <option value="entregado" {{ request('estado') === 'entregado' ? 'selected' : '' }}>
                        ✅ Entregados
                    </option>
                    <option value="no_entregado" {{ request('estado') === 'no_entregado' ? 'selected' : '' }}>
                        ❌ No entregados
                    </option>
                </select>
            </div>

            <button class="bg-gray-800 text-white px-4 py-2 rounded">
                Aplicar
            </button>

            <a href="{{ route('premios.index') }}"
               class="bg-gray-300 px-4 py-2 rounded">
                Limpiar
            </a>
        </form>

        {{-- TABLA --}}
        <div class="overflow-x-auto">
            <table class="w-full bg-white shadow rounded">
                <thead>
                    <tr class="bg-gray-200 text-left">
                        <th class="p-3">ID</th>
                        <th class="p-3">Nombre</th>
                        <th class="p-3 text-center">Entregado</th>

                    </tr>
                </thead>

                <tbody>
                    @forelse($premios as $premio)
                        <tr class="border-t">
                        <td class="p-3">{{ $premio->id }}</td>
                            <td class="p-3">{{ $premio->nombre }}</td>
                            <td class="p-3 text-center text-xl">
                                {{ $premio->entregado ? '✅' : '❌' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="p-4 text-center text-gray-500">
                                No hay premios para este filtro
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINACIÓN --}}
        <div class="mt-6">
            {{ $premios->links() }}
        </div>

    </div>
</x-app-layout>
