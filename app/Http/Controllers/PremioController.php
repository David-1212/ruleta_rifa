<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Premio;
use App\Models\Participante;
use Illuminate\Support\Facades\DB;

class PremioController extends Controller
{
    public function index(Request $request)
    {
        $query = Premio::query();

        if ($request->estado === 'entregado') {
            $query->where('entregado', true);
        }

        if ($request->estado === 'no_entregado') {
            $query->where('entregado', false);
        }

        $premios = $query
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString(); // mantiene filtros al cambiar de página

        return view('premios.index', compact('premios'));
    }

    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt'
        ]);

        $archivo = fopen($request->file('archivo'), 'r');

        while (($linea = fgetcsv($archivo, 1000, ',')) !== false) {
            if (!empty($linea[0])) {
                Premio::create([
                    'nombre' => $linea[0],
                    'entregado' => false
                ]);
            }
        }

        fclose($archivo);

        return back()->with('success', 'Premios importados correctamente');
    }
    public function borrarTodo()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Participante::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        return redirect()
            ->route('participantes.index')
            ->with('success', 'Todos los participantes fueron eliminados');
    }

}
