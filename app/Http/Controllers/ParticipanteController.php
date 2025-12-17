<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Participante;
use App\Models\Premio;
use Illuminate\Support\Facades\DB;


class ParticipanteController extends Controller
{
    public function index(Request $request)
    {
        $query = Participante::query();

        if ($request->estado === 'ganador') {
            $query->whereNotNull('premio_id');
        }

        if ($request->estado === 'no_ganador') {
            $query->whereNull('premio_id');
        }

        $participantes = $query
            ->orderByRaw('premio_id IS NULL') // 👈 salen primero los que ya jugaron
            ->orderByDesc('updated_at')       // 👈 el último que salió arriba
            ->paginate(20)
            ->withQueryString();

        $premios = Premio::pluck('nombre', 'id');

        return view('participantes.index', compact('participantes','premios'));
    }

    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt'
        ]);

        $archivo = fopen($request->file('archivo'), 'r');

        while (($linea = fgetcsv($archivo, 1000, ',')) !== false) {
            if (!empty($linea[0])) {
                Participante::create([
                    'nombre' => $linea[0],
                    'ganador' => false
                ]);
            }
        }

        fclose($archivo);

        return back()->with('success', 'Participantes importados correctamente');
    }

    public function buscar(Request $request)
    {
        $query = Participante::query();

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        $participantes = $query
            ->orderByRaw('premio_id IS NULL')
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('participantes._tabla', compact('participantes'));
    }

    
    public function borrarTodo()
    {
        \DB::transaction(function () {

            // 1️⃣ Quitar relación premio → participante
            Participante::query()->update([
                'premio_id' => null,
                'ganador' => false
            ]);

            // 2️⃣ Borrar premios correctamente
            Premio::query()->delete();
        });

        return redirect()
            ->route('premios.index')
            ->with('success', 'Todos los premios fueron eliminados');
    }

    public function obtenerEstadisticas()
    {
        return response()->json([
            'totalParticipantes' => Participante::count(),
            'totalPremios'       => Premio::count(),
            'premiosEntregados'  => Participante::whereNotNull('premio_id')->count(),
            'premiosFaltantes'   => Premio::count() - Participante::whereNotNull('premio_id')->count(),
        ]);
    }
    public function ultimosGanadores()
    {
        $ultimos = Participante::whereNotNull('premio_id')
            ->orderByDesc('updated_at')
            ->take(3)
            ->with('premio') // si tienes relación
            ->get()
            ->map(function ($p) {
                return [
                    'nombre' => $p->nombre,
                    'premio' => $p->premio->nombre ?? '—'
                ];
            });

        return response()->json($ultimos);
    }




}
