<table class="w-full bg-white shadow rounded">
    <thead>
        <tr class="bg-gray-200 text-left">
            <th class="p-3">Nombre</th>
            <th class="p-3">Estado</th>
            <th class="px-4 py-2">Premio</th>
        </tr>
    </thead>
    <tbody>
        @forelse($participantes as $p)
            <tr class="border-t">
                <td class="p-3">{{ $p->nombre }}</td>
                <td class="p-3 text-center text-xl">
                    {{ $p->ganador ? '🎉' : '—' }}
                </td>
                <td class="px-4 py-2">
                    {{ $p->premio_id 
                        ? ($premios[$p->premio_id] ?? '—') 
                        : '—' 
                    }}

                </td>

            </tr>
        @empty
            <tr>
                <td colspan="2" class="p-4 text-center text-gray-500">
                    No hay resultados
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="mt-4">
    {{ $participantes->links() }}
</div>
