<?php

namespace App\Http\Controllers;

use App\Models\Estudiante;
use App\Models\Curso;
use App\Models\ScanLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EstudianteController extends Controller
{
    public function index()
    {
        // Traemos estudiantes con su curso asignado
        $estudiantes = Estudiante::with('curso')->orderBy('id_estudiante', 'desc')->get();
        // Traemos los cursos 
        $cursos = Curso::all();

        // Calculamos las estadísticas
        $stats = [
            'total_estudiantes' => Estudiante::count(),
            'total_cursos' => Curso::count(),
            // Cuenta cuántos estudiantes distintos han escaneado hoy
            'activos_hoy' => ScanLog::whereDate('fecha_hora', now()->toDateString())
                                    ->distinct('id_estudiante')
                                    ->count('id_estudiante'),
        ];

        return Inertia::render('Estudiantes/Index', [
            'estudiantes' => $estudiantes,
            'cursos' => $cursos,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'id_curso' => 'required|exists:cursos,id_curso', // Validamos el curso
        ]);

        $estudiante = Estudiante::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'id_curso' => $request->id_curso,
            'estado' => 1,
        ]);

        $datosQr = json_encode([
            'id' => $estudiante->id_estudiante,
            'nombre' => $estudiante->nombre
        ]);

        $qrSvg = (string) QrCode::size(200)->color(17, 24, 39)->generate($datosQr);

        return redirect()->route('estudiantes.index')->with([
            'mensaje' => 'Estudiante registrado correctamente.',
            'qrCode' => $qrSvg
        ]);
    }

    // Función para actualizar
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'id_curso' => 'required|exists:cursos,id_curso',
        ]);

        $estudiante = Estudiante::findOrFail($id);
        $estudiante->update([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'id_curso' => $request->id_curso,
        ]);

        return redirect()->route('estudiantes.index')->with('mensaje', 'Estudiante actualizado.');
    }

    // Función para eliminar
    public function destroy($id)
    {
        $estudiante = Estudiante::findOrFail($id);
        $estudiante->delete();

        return redirect()->route('estudiantes.index')->with('mensaje', 'Estudiante eliminado.');
    }

    // Genera el QR y lo devuelve  para mostrarlo
    public function obtenerQr($id)
    {
        $estudiante = Estudiante::findOrFail($id);
        
        $datosQr = json_encode([
            'id' => $estudiante->id_estudiante,
            'nombre' => $estudiante->nombre
        ]);

        $qrSvg = (string) QrCode::size(250)->color(17, 24, 39)->generate($datosQr);

        return response()->json(['qr' => $qrSvg]);
    }

    //  descarga del QR como un archivo de imagen vectorial (.svg)
    public function descargarQr($id)
    {
        $estudiante = Estudiante::findOrFail($id);
        
        $datosQr = json_encode([
            'id' => $estudiante->id_estudiante,
            'nombre' => $estudiante->nombre
        ]);

        $qrSvg = (string) QrCode::size(300)->color(17, 24, 39)->generate($datosQr);

        return response()->streamDownload(function () use ($qrSvg) {
            echo $qrSvg;
        }, 'QR_' . str_replace(' ', '_', $estudiante->nombre) . '.svg', [
            'Content-Type' => 'image/svg+xml'
        ]);
    }
}