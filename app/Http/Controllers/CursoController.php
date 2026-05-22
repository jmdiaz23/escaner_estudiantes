<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Estudiante;

class CursoController extends Controller
{
   
    public function index(Request $request)
    {
        
        $search = $request->input('search');

        $query = Curso::orderBy('id_curso', 'desc');

        if ($search) {
            $query->where('nombre', 'like', "%{$search}%");
        }

       
        $cursos = $query->paginate(15)->withQueryString();
        
        return Inertia::render('Cursos/Index', [
            'cursos' => $cursos,
            'search_actual' => $search ?? '',
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
          
            'nombre' => 'required|string|max:100|unique:cursos,nombre',
        ]);

        Curso::create(['nombre' => $request->nombre]);

        return redirect()->route('cursos.index')->with('mensaje', 'Curso creado correctamente.');
    }


    public function update(Request $request, $id)
    {
        $request->validate([
           
            'nombre' => 'required|string|max:100|unique:cursos,nombre,' . $id . ',id_curso',
        ]);

        $curso = Curso::findOrFail($id);
        $curso->update(['nombre' => $request->nombre]);

        return redirect()->route('cursos.index')->with('mensaje', 'Curso actualizado correctamente.');
    }

  
    public function destroy($id)
    {
        $curso = Curso::findOrFail($id);
        $curso->delete();

        return redirect()->route('cursos.index')->with('mensaje', 'Curso eliminado correctamente.');
    }

    public function obtenerEstudiantes($id)
    {
        $estudiantes = Estudiante::where('id_curso', $id)->orderBy('nombre')->get();
        return response()->json($estudiantes);
    }

}